<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoLoginSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $demoPassword = (string) env('DEMO_LOGIN_PASSWORD', self::DEMO_PASSWORD);
        $pesertaEmail = strtolower(trim((string) env('DEMO_PESERTA_EMAIL', 'nurlaily.febriyuna@bpkp.go.id')));
        $pesertaName = (string) env('DEMO_PESERTA_NAME', 'Nurlaily Febriyuna');

        $peserta = User::query()->firstOrCreate(
            ['email' => $pesertaEmail],
            [
                'name' => $pesertaName,
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );

        $peserta->update([
            'name' => $peserta->name ?: $pesertaName,
            'password' => Hash::make($demoPassword),
            'email_verified_at' => $peserta->email_verified_at ?? now(),
        ]);

        if (! $peserta->hasRole(Roles::PESERTA)) {
            $peserta->assignRole(Roles::PESERTA);
        }

        $this->command?->info('Password demo peserta diset untuk: '.$pesertaEmail);
    }
}
