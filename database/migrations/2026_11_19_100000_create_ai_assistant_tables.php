<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI Assistant MOOC: konfigurasi, percakapan, kuota harian, log request (audit + provider)
 * dan log aktivitas admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistant_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ai_assistant_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_message_at']);
        });

        Schema::create('ai_assistant_request_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            // Snapshot identitas agar log audit tetap terbaca walau user dihapus.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role', 60)->nullable();
            $table->foreignUuid('conversation_id')->nullable()
                ->constrained('ai_assistant_conversations')->nullOnDelete();
            $table->string('session_hash', 64)->nullable();

            $table->text('question');
            $table->longText('answer')->nullable();

            $table->string('provider', 40)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('status', 20); // processing | success | failed | rejected
            $table->string('category', 40)->nullable();
            $table->json('data_categories')->nullable();
            $table->string('security_status', 20)->default('passed'); // passed | blocked | redacted
            $table->string('rejection_reason', 255)->nullable();
            $table->string('error_code', 80)->nullable();
            $table->string('error_message', 500)->nullable();

            $table->boolean('quota_counted')->default(false);
            $table->unsignedInteger('quota_used')->nullable();
            $table->unsignedInteger('quota_limit')->nullable();
            $table->date('usage_date');

            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('cache_read_tokens')->nullable();
            $table->decimal('cost_usd', 12, 6)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->timestamp('requested_at', 3);
            $table->timestamp('responded_at', 3)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'usage_date']);
            $table->index(['user_id', 'status', 'requested_at']);
            $table->index(['status', 'requested_at']);
            $table->index('requested_at');
            $table->index('usage_date');
        });

        Schema::create('ai_assistant_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_assistant_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20); // user | assistant
            $table->longText('content');
            $table->foreignUuid('request_log_id')->nullable()
                ->constrained('ai_assistant_request_logs')->nullOnDelete();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('ai_assistant_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'usage_date']);
            $table->index('usage_date');
        });

        Schema::create('ai_assistant_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('action', 60);
            $table->string('target_type', 60)->nullable();
            $table->string('target_id', 64)->nullable();
            $table->string('description', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['action', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistant_audit_logs');
        Schema::dropIfExists('ai_assistant_usages');
        Schema::dropIfExists('ai_assistant_messages');
        Schema::dropIfExists('ai_assistant_request_logs');
        Schema::dropIfExists('ai_assistant_conversations');
        Schema::dropIfExists('ai_assistant_settings');
    }
};
