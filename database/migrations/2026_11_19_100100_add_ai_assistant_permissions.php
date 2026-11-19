<?php

use App\Support\Roles;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tambah izin AI Assistant tanpa menimpa izin role yang sudah diatur admin
 * (tidak memakai syncPermissions).
 */
return new class extends Migration
{
    private const ADMIN_PERMISSIONS = [
        'ai assistant monitor view',
        'ai assistant monitor export',
        'ai assistant setting manage',
    ];

    private const PESERTA_PERMISSIONS = [
        'peserta ai assistant view',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([...self::ADMIN_PERMISSIONS, ...self::PESERTA_PERMISSIONS] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::query()->where('name', Roles::SUPER_ADMIN)->where('guard_name', 'web')->first()
            ?->givePermissionTo(self::ADMIN_PERMISSIONS);

        Role::query()->where('name', Roles::PESERTA)->where('guard_name', 'web')->first()
            ?->givePermissionTo(self::PESERTA_PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->whereIn('name', [...self::ADMIN_PERMISSIONS, ...self::PESERTA_PERMISSIONS])
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
