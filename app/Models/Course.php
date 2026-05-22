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
}
