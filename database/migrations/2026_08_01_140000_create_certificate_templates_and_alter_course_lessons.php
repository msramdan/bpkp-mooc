<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('background_image_url')->nullable();
            $table->text('signature_image_url')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_title')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->uuid('certificate_template_id')->nullable()->after('survey_id');
            $table->boolean('require_all_lessons')->default(true)->after('certificate_template_id');
            $table->uuid('prerequisite_survey_id')->nullable()->after('require_all_lessons');
            $table->unsignedSmallInteger('passing_grade')->default(0)->after('prerequisite_survey_id');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->uuid('certificate_template_id')->nullable()->after('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('certificate_template_id');
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn(['certificate_template_id', 'require_all_lessons', 'prerequisite_survey_id', 'passing_grade']);
        });

        Schema::dropIfExists('certificate_templates');
    }
};
