<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAssistantSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted', 'updated_by'];

    protected $hidden = ['value'];

    protected function casts(): array
    {
        return ['is_encrypted' => 'boolean'];
    }
}
