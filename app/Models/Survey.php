<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Survey extends Model
{
    use HasUuids;

    protected $fillable = ['title', 'description', 'is_active'];

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('urutan');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
