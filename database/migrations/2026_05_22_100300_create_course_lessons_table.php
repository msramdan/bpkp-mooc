<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->string('judul');
            $table->string('tipe', 30)->default('video');
            $table->unsignedSmallInteger('durasi_menit')->default(0);
            $table->string('video_url')->nullable();
            $table->string('file_url')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['course_module_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
