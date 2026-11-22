<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Log aktivitas (spatie/laravel-activitylog v5) + kolom konteks request agar setiap
 * aktivitas bisa ditelusuri: siapa, dari mana (IP/perangkat), halaman/aksi apa,
 * hasilnya (status), dan perubahan data sebelum/sesudah (attribute_changes).
 */
return new class extends Migration
{
    private const ADMIN_PERMISSIONS = ['activity log view', 'activity log export', 'activity log manage'];

    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            // Kolom standar spatie/laravel-activitylog v5.
            $table->id();
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            // Subject bisa UUID (kursus, materi, survey, ...) atau bigint (user) -> simpan sebagai string.
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();

            // Konteks request & snapshot pelaku (tetap terbaca walau user dihapus).
            $table->uuid('request_id')->nullable();
            $table->string('causer_name')->nullable();
            $table->string('causer_email')->nullable();
            $table->string('causer_role', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('url', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->index('created_at');
            $table->index(['causer_type', 'causer_id', 'created_at'], 'activity_log_causer_time_idx');
            $table->index(['event', 'created_at']);
            $table->index('request_id');
            $table->index('ip_address');
            $table->index('route_name');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (self::ADMIN_PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::query()->where('name', 'super_admin')->where('guard_name', 'web')->first()?->givePermissionTo(self::ADMIN_PERMISSIONS);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->whereIn('name', self::ADMIN_PERMISSIONS)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
