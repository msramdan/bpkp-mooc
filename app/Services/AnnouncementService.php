<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * Aturan pop up pengumuman:
 * - Setelah login: tampil semua pengumuman yang sedang tayang untuk user, kecuali
 *   mode "sekali" yang sudah pernah ditutup user.
 * - Di halaman lain (user sudah login sebelumnya): hanya pengumuman mode "sekali"
 *   yang BELUM pernah dilihat, agar pengumuman baru tetap sampai tanpa harus login ulang.
 */
class AnnouncementService
{
    public const SESSION_FLAG = 'announcements.after_login';

    public const POPUP_LIMIT = 10;

    /** @return Collection<int, Announcement> */
    public function popupFor(User $user, bool $afterLogin): Collection
    {
        $query = Announcement::query()->published()->forUser($user)->ordered()->limit(self::POPUP_LIMIT);

        if ($afterLogin) {
            $query->where(function ($q) use ($user): void {
                $q->where('display_mode', 'every_login')
                    ->orWhereDoesntHave('reads', fn ($r) => $r->where('user_id', $user->id)->whereNotNull('dismissed_at'));
            });
        } else {
            $query->where('display_mode', 'once')
                ->whereDoesntHave('reads', fn ($r) => $r->where('user_id', $user->id)->whereNotNull('seen_at'));
        }

        return $query->get();
    }

    /** @return Collection<int, Announcement> Semua pengumuman yang sedang tayang untuk user. */
    public function listFor(User $user): Collection
    {
        return Announcement::query()->published()->forUser($user)->ordered()->get();
    }

    public function unseenCount(User $user): int
    {
        return Announcement::query()->published()->forUser($user)
            ->whereDoesntHave('reads', fn ($r) => $r->where('user_id', $user->id)->whereNotNull('seen_at'))
            ->count();
    }

    /** @param  iterable<string>  $ids */
    public function markSeen(User $user, iterable $ids): void
    {
        $this->touch($user, $ids, ['seen_at']);
    }

    /**
     * Tutup pop up. Hanya ID yang memang sedang tayang untuk user ini yang diproses
     * (ID milik audiens lain / sudah berakhir diabaikan).
     *
     * @param  iterable<string>  $ids
     */
    public function dismiss(User $user, iterable $ids): int
    {
        return $this->touch($user, $ids, ['seen_at', 'dismissed_at']);
    }

    /** @param  iterable<string>  $ids */
    private function touch(User $user, iterable $ids, array $columns): int
    {
        $ids = collect($ids)->filter(fn ($id) => is_string($id) && $id !== '')->unique()->take(50)->values();
        if ($ids->isEmpty()) {
            return 0;
        }

        $valid = Announcement::query()->whereIn('id', $ids->all())->published()->forUser($user)->pluck('id');
        if ($valid->isEmpty()) {
            return 0;
        }

        $now = now();
        $rows = $valid->map(fn ($id) => array_merge(
            ['announcement_id' => $id, 'user_id' => $user->id, 'created_at' => $now, 'updated_at' => $now],
            array_fill_keys($columns, $now),
        ))->all();

        // Upsert = aman dari klik ganda / tab paralel (unique announcement_id + user_id).
        // seen_at pertama dipertahankan; dismissed_at diperbarui.
        AnnouncementRead::query()->upsert($rows, ['announcement_id', 'user_id'], array_merge(['updated_at'], array_diff($columns, ['seen_at'])));
        AnnouncementRead::query()->whereIn('announcement_id', $valid->all())->where('user_id', $user->id)->whereNull('seen_at')->update(['seen_at' => $now]);

        return $valid->count();
    }

    /**
     * Isi pengumuman berupa teks biasa: di-escape, baris baru dipertahankan,
     * tautan http(s) otomatis bisa diklik. Tidak ada HTML dari input yang dirender.
     */
    public static function renderBody(?string $body): HtmlString
    {
        $escaped = e((string) $body);
        $linked = preg_replace_callback(
            '~\bhttps?://[^\s<>"\']+~i',
            function (array $m): string {
                $url = rtrim($m[0], '.,);:!?');
                $tail = substr($m[0], strlen($url));

                return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer nofollow">'.$url.'</a>'.$tail;
            },
            $escaped,
        );

        return new HtmlString(nl2br($linked, false));
    }
}
