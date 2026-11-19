<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistantRequestLog extends Model
{
    use HasUuid;

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [self::STATUS_PROCESSING, self::STATUS_SUCCESS, self::STATUS_FAILED, self::STATUS_REJECTED];

    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'user_role', 'conversation_id', 'session_hash',
        'question', 'answer', 'provider', 'model', 'status', 'category', 'data_categories',
        'security_status', 'rejection_reason', 'error_code', 'error_message',
        'quota_counted', 'quota_used', 'quota_limit', 'usage_date',
        'input_tokens', 'output_tokens', 'cache_read_tokens', 'cost_usd', 'duration_ms',
        'requested_at', 'responded_at', 'ip_address', 'user_agent', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data_categories' => 'array',
            'metadata' => 'array',
            'quota_counted' => 'boolean',
            'quota_used' => 'integer',
            'quota_limit' => 'integer',
            'usage_date' => 'date',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cache_read_tokens' => 'integer',
            'cost_usd' => 'decimal:6',
            'duration_ms' => 'integer',
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiAssistantConversation::class, 'conversation_id');
    }
}
