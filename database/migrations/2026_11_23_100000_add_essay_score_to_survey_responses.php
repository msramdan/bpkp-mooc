<?php

use App\Models\SurveyResponse;
use App\Services\SurveyScoring;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai esai dipisah dari nilai PG (permintaan user, Nov 2026):
 * - total_score / max_possible_score = nilai PG & checklist (otomatis), tidak berubah.
 * - essay_score = rata-rata nilai esai (0-100) yang diberikan admin, terpisah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table): void {
            $table->decimal('essay_score', 5, 2)->nullable()->after('max_possible_score');
            $table->unsignedSmallInteger('essay_answered_count')->default(0)->after('essay_score');
            $table->unsignedSmallInteger('essay_graded_count')->default(0)->after('essay_answered_count');
            $table->timestamp('essay_graded_at')->nullable()->after('essay_graded_count');
            $table->foreignId('essay_graded_by')->nullable()->after('essay_graded_at')->constrained('users')->nullOnDelete();
        });

        // Keputusan user: nilai post test yang dipakai selalu tertinggi.
        \Illuminate\Support\Facades\DB::table('course_lessons')->where('attempt_score_policy', '!=', 'highest')->update(['attempt_score_policy' => 'highest']);

        // Isi kolom baru untuk data lama (nilai PG tidak berubah).
        SurveyResponse::query()->withoutGlobalScopes()->chunkById(200, function ($responses): void {
            foreach ($responses as $response) {
                SurveyResponse::withoutEvents(fn () => app(SurveyScoring::class)->recalculate($response));
            }
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('essay_graded_by');
            $table->dropColumn(['essay_score', 'essay_answered_count', 'essay_graded_count', 'essay_graded_at']);
        });
    }
};
