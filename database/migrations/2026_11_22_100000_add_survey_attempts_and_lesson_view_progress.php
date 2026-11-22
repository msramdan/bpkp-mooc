<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1) Percobaan kuis/survei: jenis (kuesioner/pre test/post test), maks percobaan,
 *    kebijakan nilai, dan reset per peserta (jawaban lama ditandai, tidak dihapus).
 * 2) Progres menonton video / membaca dokumen untuk penyelesaian otomatis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->string('survey_mode', 20)->default('kuesioner')->after('survey_id');
            $table->unsignedSmallInteger('max_attempts')->default(1)->after('survey_mode');
            $table->string('attempt_score_policy', 10)->default('highest')->after('max_attempts');
        });

        Schema::table('survey_responses', function (Blueprint $table): void {
            $table->unsignedSmallInteger('attempt_number')->default(1)->after('course_lesson_id');
            $table->timestamp('reset_at')->nullable()->after('grading_status');
            $table->foreignId('reset_by')->nullable()->after('reset_at')->constrained('users')->nullOnDelete();
            $table->string('reset_reason', 255)->nullable()->after('reset_by');
        });

        // Data lama: jika ada lebih dari satu jawaban per peserta per materi, beri nomor urut.
        $duplicates = DB::table('survey_responses')
            ->select('course_lesson_id', 'user_id')
            ->whereNotNull('course_lesson_id')
            ->groupBy('course_lesson_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $ids = DB::table('survey_responses')
                ->where('course_lesson_id', $dup->course_lesson_id)
                ->where('user_id', $dup->user_id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $i => $id) {
                DB::table('survey_responses')->where('id', $id)->update(['attempt_number' => $i + 1]);
            }
        }

        // Index tunggal eksplisit untuk FK course_lesson_id & user_id, supaya FK tidak
        // "meminjam" unique komposit di bawah (agar migration bisa di-rollback).
        Schema::table('survey_responses', function (Blueprint $table): void {
            if (! Schema::hasIndex('survey_responses', 'survey_responses_course_lesson_id_idx')) {
                $table->index('course_lesson_id', 'survey_responses_course_lesson_id_idx');
            }
            if (! Schema::hasIndex('survey_responses', 'survey_responses_user_id_idx')) {
                $table->index('user_id', 'survey_responses_user_id_idx');
            }
        });

        Schema::table('survey_responses', function (Blueprint $table): void {
            // Penjaga terakhir race condition: satu nomor percobaan per peserta per materi.
            $table->unique(['course_lesson_id', 'user_id', 'attempt_number'], 'survey_responses_attempt_unique');
            $table->index(['user_id', 'course_lesson_id', 'reset_at'], 'survey_responses_active_idx');
        });

        Schema::create('lesson_view_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('course_lesson_id')->constrained('course_lessons')->cascadeOnDelete();
            $table->string('kind', 20); // video | document
            $table->decimal('duration_seconds', 10, 2)->nullable();
            $table->decimal('max_position', 10, 2)->default(0);
            $table->decimal('last_position', 10, 2)->default(0);
            $table->decimal('watched_seconds', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_pages')->nullable();
            $table->json('pages_seen')->nullable();
            $table->unsignedSmallInteger('pages_seen_count')->default(0);
            $table->unsignedInteger('heartbeats')->default(0);
            $table->unsignedInteger('rejected_jumps')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_view_progress');

        Schema::table('survey_responses', function (Blueprint $table): void {
            // Index tunggal untuk FK dibiarkan (dibuat di up()) agar unique bisa di-drop.
            $table->dropUnique('survey_responses_attempt_unique');
            $table->dropIndex('survey_responses_active_idx');
            $table->dropConstrainedForeignId('reset_by');
            $table->dropColumn(['attempt_number', 'reset_at', 'reset_reason']);
        });

        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->dropColumn(['survey_mode', 'max_attempts', 'attempt_score_policy']);
        });
    }
};
