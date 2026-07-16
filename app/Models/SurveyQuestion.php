<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SurveyQuestion extends Model
{
    use HasUuids;

    protected $fillable = ['survey_id', 'type', 'question_text', 'is_required', 'urutan'];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function options()
    {
        return $this->hasMany(SurveyOption::class)->orderBy('urutan');
    }
}
