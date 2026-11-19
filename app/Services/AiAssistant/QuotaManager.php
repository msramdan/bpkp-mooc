<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantRequestLog;
use App\Models\AiAssistantUsage;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Kuota harian per user (bukan per session/perangkat).
 *
 * Konsumsi atomik: row kuota user-hari dikunci (SELECT ... FOR UPDATE), lalu
 * jumlah terpakai + request yang sedang diproses dibandingkan dengan limit.
 * Request paralel antre di lock yang sama sehingga tidak bisa melewati batas.
 * Kuota baru benar-benar terpakai (commit) setelah AI berhasil menjawab;
 * request gagal/timeout melepas slotnya tanpa memotong kuota.
 */
class QuotaManager
{
    public function __construct(private readonly AiAssistantSettings $settings) {}

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->settings->timezone());
    }

    /** Tanggal kuota: hari lokal (timezone setting) yang dimulai pada jam reset. */
    public function usageDate(?CarbonImmutable $at = null): string
    {
        $at = ($at ?? $this->now())->setTimezone($this->settings->timezone());

        return $at->subHours($this->resetHour())->toDateString();
    }

    public function nextResetAt(?CarbonImmutable $at = null): CarbonImmutable
    {
        $at = ($at ?? $this->now())->setTimezone($this->settings->timezone());
        $reset = CarbonImmutable::parse($this->usageDate($at), $this->settings->timezone())
            ->addDay()
            ->setTime($this->resetHour(), 0);

        return $reset->greaterThan($at) ? $reset : $reset->addDay();
    }

    public function limit(): int
    {
        return max(1, $this->settings->int('daily_limit'));
    }

    public function limitEnabled(): bool
    {
        return $this->settings->bool('usage_limit_enabled');
    }

    /**
     * @return array{used:int, limit:int, remaining:int|null, limit_enabled:bool, usage_date:string, reset_at:CarbonImmutable, reset_label:string}
     */
    public function status(User $user): array
    {
        $date = $this->usageDate();
        $used = (int) AiAssistantUsage::query()
            ->where('user_id', $user->id)
            ->where('usage_date', $date)
            ->value('used_count');

        $limit = $this->limit();
        $resetAt = $this->nextResetAt();

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => $this->limitEnabled() ? max(0, $limit - $used) : null,
            'limit_enabled' => $this->limitEnabled(),
            'usage_date' => $date,
            'reset_at' => $resetAt,
            'reset_label' => $this->resetLabel($resetAt),
        ];
    }

    public function resetLabel(CarbonImmutable $resetAt): string
    {
        $now = $this->now();
        $abbr = $this->timezoneAbbreviation();
        $time = $resetAt->format('H:i');

        if ($resetAt->isSameDay($now)) {
            return __('Hari ini pukul :time :tz', ['time' => $time, 'tz' => $abbr]);
        }

        if ($resetAt->isSameDay($now->addDay())) {
            return __('Besok pukul :time :tz', ['time' => $time, 'tz' => $abbr]);
        }

        return $resetAt->translatedFormat('d M Y').' '.$time.' '.$abbr;
    }

    public function timezoneAbbreviation(): string
    {
        return match ($this->settings->timezone()) {
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => 'WIB',
        };
    }

    /**
     * Pesan slot kuota secara atomik. $createLog dipanggil di dalam transaksi yang sama
     * (saat lock masih dipegang) untuk membuat log berstatus "processing".
     *
     * @param  Closure(string $usageDate, int $used, int $limit): AiAssistantRequestLog  $createLog
     * @return array{allowed:bool, log:?AiAssistantRequestLog, used:int, limit:int, usage_date:string}
     */
    public function reserve(User $user, Closure $createLog): array
    {
        $date = $this->usageDate();
        $limit = $this->limit();
        $staleBefore = now()->subSeconds($this->inflightWindowSeconds());

        // Baris kuota dibuat di luar transaksi: insertOrIgnore di dalam transaksi mengambil
        // shared lock yang bisa deadlock dengan FOR UPDATE dari request paralel.
        $this->ensureUsageRow($user, $date);

        // Deadlock/serialization failure (InnoDB) di-retry otomatis.
        return DB::transaction(function () use ($user, $date, $limit, $staleBefore, $createLog): array {
            $usage = AiAssistantUsage::query()
                ->where('user_id', $user->id)
                ->where('usage_date', $date)
                ->lockForUpdate()
                ->firstOrFail();

            // Request yang macet (proses PHP mati) tidak boleh mengunci kuota selamanya.
            AiAssistantRequestLog::query()
                ->where('user_id', $user->id)
                ->where('status', AiAssistantRequestLog::STATUS_PROCESSING)
                ->where('requested_at', '<', $staleBefore)
                ->update([
                    'status' => AiAssistantRequestLog::STATUS_FAILED,
                    'error_code' => 'stale_request',
                    'error_message' => 'Request tidak selesai (melewati batas waktu proses).',
                ]);

            $inflight = AiAssistantRequestLog::query()
                ->where('user_id', $user->id)
                ->where('status', AiAssistantRequestLog::STATUS_PROCESSING)
                ->count();

            $used = (int) $usage->used_count;

            if ($this->limitEnabled() && ($used + $inflight) >= $limit) {
                return ['allowed' => false, 'log' => null, 'used' => $used, 'limit' => $limit, 'usage_date' => $date];
            }

            $log = $createLog($date, $used, $limit);

            return ['allowed' => true, 'log' => $log, 'used' => $used, 'limit' => $limit, 'usage_date' => $date];
        }, 5);
    }

    private function ensureUsageRow(User $user, string $date): void
    {
        $exists = AiAssistantUsage::query()->where('user_id', $user->id)->where('usage_date', $date)->exists();

        if (! $exists) {
            AiAssistantUsage::query()->insertOrIgnore([
                'user_id' => $user->id,
                'usage_date' => $date,
                'used_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** Tandai satu penggunaan berhasil. Mengembalikan jumlah terpakai setelah commit. */
    public function commit(User $user, AiAssistantRequestLog $log, array $attributes): int
    {
        return DB::transaction(function () use ($user, $log, $attributes): int {
            $usage = AiAssistantUsage::query()
                ->where('user_id', $user->id)
                ->where('usage_date', $log->usage_date->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            $usage->used_count = $usage->used_count + 1;
            $usage->last_used_at = now();
            $usage->save();

            $log->fill($attributes + [
                'status' => AiAssistantRequestLog::STATUS_SUCCESS,
                'quota_counted' => true,
                'quota_used' => $usage->used_count,
            ])->save();

            return $usage->used_count;
        }, 5);
    }

    /** Lepas slot tanpa memotong kuota (gagal / ditolak setelah reservasi). */
    public function release(AiAssistantRequestLog $log, array $attributes): void
    {
        $log->fill($attributes + ['quota_counted' => false])->save();
    }

    private function resetHour(): int
    {
        return min(23, max(0, $this->settings->int('reset_hour')));
    }

    private function inflightWindowSeconds(): int
    {
        // Batas waktu provider x (1 + retry) + margin.
        return max(30, $this->settings->int('request_timeout')) * 2 + 60;
    }
}
