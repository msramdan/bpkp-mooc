<?php

namespace Database\Seeders;

use App\Models\LearningTag;
use Illuminate\Database\Seeder;

class LearningTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Pemula'],
            ['name' => 'Sertifikasi'],
        ];

        foreach ($tags as $tag) {
            LearningTag::firstOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
