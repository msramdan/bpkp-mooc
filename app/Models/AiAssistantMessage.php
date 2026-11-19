<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistantMessage extends Model
{
    use HasUuid;

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = ['conversation_id', 'user_id', 'role', 'content', 'request_log_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiAssistantConversation::class, 'conversation_id');
    }

    public function requestLog(): BelongsTo
    {
        return $this->belongsTo(AiAssistantRequestLog::class, 'request_log_id');
    }
}
