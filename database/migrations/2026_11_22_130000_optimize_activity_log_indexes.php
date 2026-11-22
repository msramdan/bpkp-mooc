<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index log aktivitas untuk volume besar.
 * Semua daftar diurutkan ORDER BY id DESC. Di InnoDB setiap index sekunder sudah memuat
 * primary key, jadi index (kolom filter) saja sudah cukup untuk "filter + urut id" tanpa
 * filesort. Kolom created_at di akhir index justru memaksa sort ulang, maka diganti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->index(['causer_type', 'causer_id'], 'activity_log_causer_idx');
            $table->index('event', 'activity_log_event_idx');
        });

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropIndex('activity_log_causer_time_idx');
            $table->dropIndex('activity_log_event_created_at_index');
            // Index bawaan nullableMorphs('causer') digantikan activity_log_causer_idx.
            $table->dropIndex('causer');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->index(['causer_type', 'causer_id', 'created_at'], 'activity_log_causer_time_idx');
            $table->index(['event', 'created_at']);
        });

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropIndex('activity_log_causer_idx');
            $table->dropIndex('activity_log_event_idx');
        });
    }
};
