<?php

namespace Database\Seeders;

use App\Models\LearningCategory;
use Illuminate\Database\Seeder;

class LearningCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Audit Internal'],
            ['name' => 'Manajemen Risiko'],
            ['name' => 'Sistem Pengendalian Intern Pemerintah (SPIP)'],
            ['name' => 'Akuntabilitas & Keuangan Negara'],
            ['name' => 'Audit Investigatif & Anti-Fraud'],
            ['name' => 'Tata Kelola & Good Governance'],
            ['name' => 'Teknologi Informasi & Pengawasan Digital'],
        ];

        foreach ($categories as $category) {
            LearningCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
