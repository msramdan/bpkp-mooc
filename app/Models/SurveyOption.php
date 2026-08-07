<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SurveyOption extends Model
{
    use HasUuids;

    protected $fillable = ['survey_question_id', 'option_text', 'urutan', 'score_value', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_value' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
