<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $rolePeserta = Role::firstOrCreate(['name' => 'peserta']);

        foreach (config(key: 'permission.permissions') as $permission) {
            foreach ($permission['access'] as $access) {
                Permission::firstOrCreate(['name' => $access]);
            }
        }

        $pesertaPermissions = Permission::query()
            ->where('name', 'like', 'peserta %')
            ->pluck('name')
            ->all();

        $adminPermissions = Permission::query()
            ->where('name', 'not like', 'peserta %')
            ->pluck('name')
            ->all();

        $roleAdmin->syncPermissions($adminPermissions);
        $rolePeserta->syncPermissions($pesertaPermissions);

        $userAdmin = User::where('email', 'admin@example.com')->first();
        if ($userAdmin) {
            $userAdmin->syncRoles(['admin']);
        }
    }
}
