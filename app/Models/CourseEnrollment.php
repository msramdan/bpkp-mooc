<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    use HasUuid;
    protected $fillable = [
        'user_id',
        'course_id',
        'progress',
        'modul_selesai',
        'status',
        'deadline',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'modul_selesai' => 'integer',
            'deadline' => 'date',
            'enrolled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function modulLabel(): string
    {
        return $this->modul_selesai.' / '.$this->course->modul_total;
    }
}
