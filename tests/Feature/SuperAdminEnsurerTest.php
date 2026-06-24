<?php

namespace Tests\Feature;

use App\Services\SuperAdminEnsurer;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminEnsurerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_is_created_with_super_admin_role_only(): void
    {
        config([
            'roles.super_admin.name' => 'Agus Reza Pahlevi',
            'roles.super_admin.email' => 'agus.pahlevi@bpkp.go.id',
            'roles.super_admin.password' => 'SecretPass123!',
        ]);

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $user = app(SuperAdminEnsurer::class)->ensure();

        $this->assertSame('agus.pahlevi@bpkp.go.id', $user->email);
        $this->assertTrue($user->hasRole(Roles::SUPER_ADMIN));
        $this->assertFalse($user->hasRole(Roles::PESERTA));
    }
}
