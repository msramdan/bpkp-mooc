<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('survey_options', function (Blueprint $table) {
            $table->unsignedSmallInteger('score_value')->default(0)->after('option_text');
            $table->boolean('is_correct')->default(false)->after('score_value');
        });

        Schema::table('survey_answers', function (Blueprint $table) {
            $table->unsignedSmallInteger('score')->default(0)->after('answer_text');
            $table->boolean('is_graded')->default(true)->after('score');
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->decimal('total_score', 10, 2)->default(0)->after('course_lesson_id');
            $table->decimal('max_possible_score', 10, 2)->default(0)->after('total_score');
            $table->string('grading_status')->default('completed')->after('max_possible_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_options', function (Blueprint $table) {
            $table->dropColumn(['score_value', 'is_correct']);
        });

        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropColumn(['score', 'is_graded']);
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['total_score', 'max_possible_score', 'grading_status']);
        });
    }
};
