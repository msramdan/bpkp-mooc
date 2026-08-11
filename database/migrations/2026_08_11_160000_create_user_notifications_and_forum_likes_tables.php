<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->string('body', 500)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('course_forum_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('likeable_type', 40);
            $table->uuid('likeable_id');
            $table->timestamps();

            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'forum_likes_user_target_unique');
            $table->index(['likeable_type', 'likeable_id'], 'forum_likes_target_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_forum_likes');
        Schema::dropIfExists('user_notifications');
    }
};
