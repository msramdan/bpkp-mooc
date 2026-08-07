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
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->decimal('total_score', 10, 2)->default(0)->change();
            $table->decimal('max_possible_score', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->decimal('total_score', 5, 2)->default(0)->change();
            $table->decimal('max_possible_score', 5, 2)->default(0)->change();
        });
    }
};
