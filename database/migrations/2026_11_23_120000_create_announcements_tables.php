<?php

use App\Support\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Pengumuman dari Super Admin: tampil sebagai pop up setelah login, bisa dijadwalkan
 * (mulai/berakhir) dan dinyalakan / dimatikan. Izin ditambahkan tanpa menimpa izin role lain.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['announcement view', 'announcement manage'];

    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title', 150);
            $table->text('body')->nullable();
            $table->string('category', 20)->default('info');      // info | penting | mendesak
            $table->string('audience', 20)->default('peserta');   // peserta | all | admin
            $table->string('display_mode', 20)->default('once');  // once | every_login
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();           // null = langsung tayang
            $table->timestamp('ends_at')->nullable();             // null = tanpa batas
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label', 60)->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 120)->nullable();
            $table->unsignedInteger('attachment_size')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Query pop up: aktif + dalam rentang tayang.
            $table->index(['is_active', 'starts_at', 'ends_at'], 'announcements_visible_idx');
        });

        Schema::create('announcement_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
            $table->index('user_id');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::query()->where('name', Roles::SUPER_ADMIN)->where('guard_name', 'web')->first()
            ?->givePermissionTo(self::PERMISSIONS);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
