<?php

namespace App\Services;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminEnsurer
{
    public function ensure(): User
    {
        $config = config('roles.super_admin');
        $email = strtolower(trim((string) ($config['email'] ?? '')));
        $name = trim((string) ($config['name'] ?? 'Super Admin'));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('SUPER_ADMIN_EMAIL tidak valid di config/roles.php');
        }

        $role = Role::firstOrCreate(
            ['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']
        );

        $user = User::query()->where('email', $email)->first();

        $configuredPassword = $config['password'] ?? null;
        $hasConfiguredPassword = $configuredPassword !== null && $configuredPassword !== '';
        $password = $hasConfiguredPassword ? $configuredPassword : Str::password(16);

        if ($user) {
            $updates = ['name' => $name];
            if ($hasConfiguredPassword) {
                $updates['password'] = Hash::make($configuredPassword);
            }
            $user->update($updates);
        } else {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
            ]);
        }

        $user->syncRoles([$role]);

        return $user;
    }
}
