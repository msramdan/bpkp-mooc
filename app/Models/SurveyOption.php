<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SurveyOption extends Model
{
    use HasUuids;

    protected $fillable = ['survey_question_id', 'option_text', 'urutan'];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
