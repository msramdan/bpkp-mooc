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
