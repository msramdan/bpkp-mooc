<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasUuid;

    protected $fillable = [
        'kode',
        'judul',
        'slug',
        'kategori',
        'id_number',
        'instruktur',
        'thumbnail',
        'durasi_jam',
        'modul_total',
        'level',
        'rating',
        'deskripsi',
        'starts_at',
        'ends_at',
        'ends_at_enabled',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'ends_at_enabled' => 'boolean',
            'durasi_jam' => 'integer',
            'modul_total' => 'integer',
            'rating' => 'float',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /** Topics (UI label); stored as course_modules. */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('urutan');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LearningTag::class, 'course_learning_tag')
            ->orderBy('name');
    }

    public function topicsCount(): int
    {
        if ($this->relationLoaded('modules')) {
            return $this->modules->count();
        }

        return (int) ($this->modules_count ?? $this->modul_total ?? 0);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnailUrl();
    }

    public function thumbnailUrl(): string
    {
        if ($this->hasUsableThumbnail()) {
            $thumbnail = trim((string) $this->thumbnail);

            if (str_starts_with($thumbnail, 'http://') || str_starts_with($thumbnail, 'https://') || str_starts_with($thumbnail, '/')) {
                return $thumbnail;
            }

            return Storage::disk('public')->url($thumbnail);
        }

        return asset((string) config('mooc.course_placeholder', 'images/course-no-image.png'));
    }

    public function hasUsableThumbnail(): bool
    {
        $thumbnail = trim((string) ($this->thumbnail ?? ''));

        if ($thumbnail === '') {
            return false;
        }

        if (str_contains($thumbnail, 'pluginfile.php')) {
            return false;
        }

        if (str_contains($thumbnail, 'images.unsplash.com')) {
            return false;
        }

        return true;
    }
}
