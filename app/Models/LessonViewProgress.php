<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Progres menonton video / membaca dokumen satu peserta pada satu materi.
 * Nilai-nilainya hanya diubah oleh LessonViewTracker (divalidasi server).
 */
class LessonViewProgress extends Model
{
    protected $table = 'lesson_view_progress';

    protected $fillable = [
        'user_id', 'course_lesson_id', 'kind', 'duration_seconds', 'max_position', 'last_position', 'watched_seconds',
        'total_pages', 'pages_seen', 'pages_seen_count', 'heartbeats', 'rejected_jumps',
        'started_at', 'last_heartbeat_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'float',
            'max_position' => 'float',
            'last_position' => 'float',
            'watched_seconds' => 'float',
            'total_pages' => 'integer',
            'pages_seen' => 'array',
            'pages_seen_count' => 'integer',
            'heartbeats' => 'integer',
            'rejected_jumps' => 'integer',
            'started_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
