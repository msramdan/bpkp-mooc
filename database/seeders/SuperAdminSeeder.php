<?php

namespace Database\Seeders;

use App\Services\SuperAdminEnsurer;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(SuperAdminEnsurer::class)->ensure();
    }
}
