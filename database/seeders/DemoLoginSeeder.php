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

        $peserta = User::query()->where('email', $pesertaEmail)->first();

        if ($peserta === null) {
            $this->command?->warn("Peserta demo tidak ditemukan: {$pesertaEmail}. Jalankan import sample dulu.");

            return;
        }

        $peserta->update([
            'password' => Hash::make($demoPassword),
            'email_verified_at' => $peserta->email_verified_at ?? now(),
        ]);

        if (! $peserta->hasRole(Roles::PESERTA)) {
            $peserta->assignRole(Roles::PESERTA);
        }

        $this->command?->info('Password demo peserta diset untuk: '.$pesertaEmail);
    }
}
