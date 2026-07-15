<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LearningTag extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_learning_tag');
    }
}
