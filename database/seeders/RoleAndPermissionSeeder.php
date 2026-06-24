<?php

namespace Database\Seeders;

use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleSuperAdmin = Role::firstOrCreate(
            ['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']
        );
        $rolePeserta = Role::firstOrCreate(
            ['name' => Roles::PESERTA, 'guard_name' => 'web']
        );

        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->where('name', 'admin')
                ->where('guard_name', 'web')
                ->update(['name' => Roles::SUPER_ADMIN]);
        }

        foreach (config(key: 'permission.permissions') as $permission) {
            foreach ($permission['access'] as $access) {
                Permission::firstOrCreate(['name' => $access, 'guard_name' => 'web']);
            }
        }

        $pesertaPermissions = Permission::query()
            ->where('name', 'like', 'peserta %')
            ->pluck('name')
            ->all();

        $superAdminPermissions = Permission::query()
            ->where('name', 'not like', 'peserta %')
            ->pluck('name')
            ->all();

        $roleSuperAdmin->syncPermissions($superAdminPermissions);
        $rolePeserta->syncPermissions($pesertaPermissions);
    }
}
