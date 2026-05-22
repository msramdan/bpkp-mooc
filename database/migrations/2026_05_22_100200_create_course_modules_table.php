<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('durasi_menit')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
