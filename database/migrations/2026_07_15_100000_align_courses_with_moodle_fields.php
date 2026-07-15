<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'id_number')) {
                $table->string('id_number', 100)->nullable()->after('kode');
            }
            if (! Schema::hasColumn('courses', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('courses', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }
            if (! Schema::hasColumn('courses', 'ends_at_enabled')) {
                $table->boolean('ends_at_enabled')->default(false)->after('ends_at');
            }
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('course_lessons', 'show_description')) {
                $table->boolean('show_description')->default(false)->after('body');
            }
        });

        // Normalize legacy lesson types toward simplified palette (safe for re-import).
        if (Schema::hasTable('course_lessons')) {
            DB::table('course_lessons')->whereIn('tipe', ['dokumen', 'reading'])->update(['tipe' => 'berkas']);
            DB::table('course_lessons')->where('tipe', 'live')->update(['tipe' => 'url']);
            DB::table('course_lessons')->where('tipe', 'kuis')->update(['tipe' => 'pre_test']);
        }
    }

    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            if (Schema::hasColumn('course_lessons', 'show_description')) {
                $table->dropColumn('show_description');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            $columns = collect(['id_number', 'starts_at', 'ends_at', 'ends_at_enabled'])
                ->filter(fn (string $col) => Schema::hasColumn('courses', $col))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
