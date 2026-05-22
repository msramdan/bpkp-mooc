<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PesertaSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $names = [
        'Ahmad Fauzi',
        'Siti Nurhaliza',
        'Budi Santoso',
        'Dewi Lestari',
        'Eko Prasetyo',
        'Fitriani Rahma',
        'Gunawan Hidayat',
        'Hani Permata',
        'Indra Wijaya',
        'Joko Susilo',
        'Kartika Sari',
        'Lukman Hakim',
        'Maya Anggraini',
        'Nanda Pratama',
        'Oki Ramadhan',
        'Putri Maharani',
        'Rizki Maulana',
        'Sari Dewi',
        'Taufik Hidayat',
        'Umi Kalsum',
        'Vina Oktaviani',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password

        foreach ($this->names as $index => $name) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            $user = User::firstOrCreate(
                ['email' => "peserta{$number}@bpkp-mooc.test"],
                [
                    'name' => $name,
                    'email_verified_at' => now(),
                    'password' => $password,
                    'remember_token' => Str::random(10),
                ]
            );

            $user->syncRoles(['peserta']);
        }
    }
}
