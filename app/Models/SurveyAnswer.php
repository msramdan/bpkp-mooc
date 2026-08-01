<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SurveyAnswer extends Model
{
    use HasUuids;

    protected $fillable = ['survey_response_id', 'survey_question_id', 'answer_text', 'survey_option_id', 'score', 'is_graded'];

    protected $casts = [
        'is_graded' => 'boolean',
        'score' => 'integer',
    ];

    public function response()
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    public function option()
    {
        return $this->belongsTo(SurveyOption::class, 'survey_option_id');
    }
}
