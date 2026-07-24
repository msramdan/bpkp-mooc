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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignUuid('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->text('answer_text')->nullable(); // Untuk jawaban tipe text
            $table->foreignUuid('survey_option_id')->nullable()->constrained('survey_options')->cascadeOnDelete(); // Untuk jawaban radio/checkbox
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
