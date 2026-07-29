<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_forum_open')->default(false)->after('instansi');
        });

        Schema::create('course_forum_threads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'last_activity_at']);
        });

        Schema::create('course_forum_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('thread_id')->constrained('course_forum_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_forum_replies');
        Schema::dropIfExists('course_forum_threads');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('is_forum_open');
        });
    }
};
