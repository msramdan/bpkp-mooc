<?php

namespace App\Models\Concerns;

use App\Support\Activity\ActivityDescriber;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Catat setiap tambah/ubah/hapus model ke log aktivitas dengan nilai sebelum & sesudah.
 * Hanya field yang berubah yang dicatat; timestamp & field sensitif dikecualikan.
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('data')
            ->logAll()
            ->logOnlyDirty()
            ->logExcept(array_merge(['created_at', 'updated_at'], $this->activityLogExcept()))
            ->dontLogEmptyChanges();
    }

    /** Field tambahan yang tidak dicatat untuk model tertentu. */
    protected function activityLogExcept(): array
    {
        return [];
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ActivityDescriber::forModel($this, $eventName);
    }
}
