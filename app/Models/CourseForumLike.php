<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseForumLike extends Model
{
    use HasUuid;

    public const TYPE_THREAD = 'thread';

    public const TYPE_REPLY = 'reply';

    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
