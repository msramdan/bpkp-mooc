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

        foreach (config(key: 'permission.permissions') as $permission) {
            foreach ($permission['access'] as $access) {
                Permission::firstOrCreate(['name' => $access]);
            }
        }

        $userAdmin = User::first();
        if ($userAdmin) {
            $userAdmin->assignRole('admin');
        }
        $roleAdmin->syncPermissions(Permission::all());
    }
}
