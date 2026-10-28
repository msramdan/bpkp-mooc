<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tag kursus tidak lagi dikelola di MOOC (CRUD learning_tags), tetapi mengambil
 * nama kompetensi dari Aplikasi TNA. Kursus cukup menyimpan nama kompetensinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['course_id', 'name']);
        });

        Schema::dropIfExists('course_learning_tag');
        Schema::dropIfExists('learning_tags');

        $this->deleteLearningTagPermissions();
    }

    public function down(): void
    {
        Schema::create('learning_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 120)->unique();
            $table->timestamps();
        });

        Schema::create('course_learning_tag', function (Blueprint $table) {
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignUuid('learning_tag_id')->constrained('learning_tags')->cascadeOnDelete();
            $table->primary(['course_id', 'learning_tag_id']);
        });

        Schema::dropIfExists('course_tags');
    }

    private function deleteLearningTagPermissions(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (! Schema::hasTable($permissionsTable)) {
            return;
        }

        DB::table($permissionsTable)->where('name', 'like', 'learning tag %')->delete();
        app('cache')->forget(config('permission.cache.key', 'spatie.permission.cache'));
    }
};
