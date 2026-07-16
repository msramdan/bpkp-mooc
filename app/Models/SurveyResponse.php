<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SurveyResponse extends Model
{
    use HasUuids;

    protected $fillable = ['survey_id', 'user_id', 'course_lesson_id'];

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

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
