<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasUuid;
    protected $fillable = [
        'kode',
        'judul',
        'slug',
        'kategori',
        'instruktur',
        'thumbnail',
        'durasi_jam',
        'modul_total',
        'level',
        'rating',
        'deskripsi',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'durasi_jam' => 'integer',
            'modul_total' => 'integer',
            'rating' => 'float',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('urutan');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnailUrl();
    }

    public function thumbnailUrl(): string
    {
        if ($this->hasUsableThumbnail()) {
            return trim((string) $this->thumbnail);
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
