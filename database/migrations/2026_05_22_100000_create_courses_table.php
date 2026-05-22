<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 120);
            $table->string('instruktur');
            $table->string('thumbnail');
            $table->unsignedSmallInteger('durasi_jam')->default(0);
            $table->unsignedSmallInteger('modul_total')->default(1);
            $table->string('level', 30)->default('Pemula');
            $table->decimal('rating', 2, 1)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
