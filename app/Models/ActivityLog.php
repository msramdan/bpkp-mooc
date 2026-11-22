<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * Aktivitas (spatie/laravel-activitylog) + konteks request.
 *
 * log_name: request (halaman/aksi) | auth (login/logout) | data (perubahan data).
 */
class ActivityLog extends Activity
{
    public const LOG_NAMES = [
        'request' => 'Halaman & Aksi',
        'auth' => 'Autentikasi',
        'data' => 'Perubahan Data',
    ];

    protected function casts(): array
    {
        return parent::casts() + [
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * Isolasi data: hanya aktivitas yang DILAKUKAN oleh user ini.
     * Dipakai halaman "Aktivitas Saya" — user selalu dari sesi login, bukan dari input.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('causer_type', $user->getMorphClass())->where('causer_id', $user->getKey());
    }

    /**
     * Filter rentang waktu yang tetap cepat di tabel besar.
     * ID bertambah seiring waktu (auto increment saat aktivitas dicatat), jadi rentang
     * waktu diterjemahkan ke rentang ID lewat SATU lookup index created_at, lalu query
     * utama cukup memindai primary key yang sudah urut (tanpa filesort/COUNT besar).
     * Kondisi created_at tetap dipertahankan agar hasil tepat di batas rentang.
     */
    public function scopeCreatedBetween(Builder $query, ?\DateTimeInterface $from, ?\DateTimeInterface $to): Builder
    {
        if ($from) {
            $floor = static::query()->where('created_at', '>=', $from)->orderBy('created_at')->orderBy('id')->value('id');
            $floor === null ? $query->whereRaw('1 = 0') : $query->where('id', '>=', $floor)->where('created_at', '>=', $from);
        }

        if ($to) {
            $ceil = static::query()->where('created_at', '<=', $to)->orderByDesc('created_at')->orderByDesc('id')->value('id');
            $ceil === null ? $query->whereRaw('1 = 0') : $query->where('id', '<=', $ceil)->where('created_at', '<=', $to);
        }

        return $query;
    }

    public function before(): array
    {
        return (array) ($this->attribute_changes?->get('old') ?? []);
    }

    public function after(): array
    {
        return (array) ($this->attribute_changes?->get('attributes') ?? []);
    }

    /** Daftar field yang berubah: [field => [before, after]]. */
    public function diff(): array
    {
        $before = $this->before();
        $after = $this->after();
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $rows = [];

        foreach ($keys as $key) {
            $rows[$key] = [$before[$key] ?? null, $after[$key] ?? null];
        }

        return $rows;
    }

    public function subjectLabel(): string
    {
        if (! $this->subject_type) {
            return '-';
        }

        return \App\Support\Activity\ActivityDescriber::modelLabel($this->subject_type);
    }
}
