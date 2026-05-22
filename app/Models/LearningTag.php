<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class LearningTag extends Model
{
    use HasUuid;
    protected $fillable = [
        'name',
    ];
}
