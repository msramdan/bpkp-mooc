<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_id',
        'urutan',
        'judul',
        'deskripsi',
        'durasi_menit',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'durasi_menit' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('urutan');
    }
}
