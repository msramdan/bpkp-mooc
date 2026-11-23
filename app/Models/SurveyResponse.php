<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasUuids, Concerns\RecordsActivity;

    protected $fillable = [
        'survey_id', 'user_id', 'course_lesson_id', 'attempt_number',
        'total_score', 'max_possible_score', 'grading_status',
        'essay_score', 'essay_answered_count', 'essay_graded_count', 'essay_graded_at', 'essay_graded_by',
        'reset_at', 'reset_by', 'reset_reason',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'max_possible_score' => 'decimal:2',
        'attempt_number' => 'integer',
        'reset_at' => 'datetime',
        'essay_score' => 'decimal:2',
        'essay_answered_count' => 'integer',
        'essay_graded_count' => 'integer',
        'essay_graded_at' => 'datetime',
    ];

    /** Percobaan yang masih berlaku (belum di-reset admin). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('reset_at');
    }

    public function isReset(): bool
    {
        return $this->reset_at !== null;
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function resetter()
    {
        return $this->belongsTo(User::class, 'reset_by');
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
