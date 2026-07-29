<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->boolean('assignment_allow_text')->default(false)->after('survey_id');
            $table->boolean('assignment_allow_file')->default(true)->after('assignment_allow_text');
            $table->boolean('assignment_allow_link')->default(false)->after('assignment_allow_file');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->text('submission_text')->nullable()->after('course_lesson_id');
            $table->string('submission_link', 2048)->nullable()->after('submission_text');
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['submission_text', 'submission_link']);
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn(['assignment_allow_text', 'assignment_allow_file', 'assignment_allow_link']);
        });
    }
};
