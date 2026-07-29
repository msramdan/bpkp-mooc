<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseForumReply extends Model
{
    use HasUuid;

    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(CourseForumThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
