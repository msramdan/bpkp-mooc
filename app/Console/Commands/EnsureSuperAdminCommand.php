<?php

namespace App\Console\Commands;

use App\Services\SuperAdminEnsurer;
use Illuminate\Console\Command;

class EnsureSuperAdminCommand extends Command
{
    protected $signature = 'mooc:ensure-super-admin';

    protected $description = 'Pastikan super admin MOOC ada (Agus Reza Pahlevi)';

    public function handle(SuperAdminEnsurer $ensurer): int
    {
        $user = $ensurer->ensure();

        $this->info("Super admin: {$user->name} <{$user->email}>");

        if (config('roles.super_admin.password') === null || config('roles.super_admin.password') === '') {
            $this->warn('SUPER_ADMIN_PASSWORD kosong — password acak dibuat saat user baru. Set di .env untuk password tetap.');
        }

        return self::SUCCESS;
    }
}
