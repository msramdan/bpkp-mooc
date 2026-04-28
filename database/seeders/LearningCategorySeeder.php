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
        ];

        foreach ($categories as $category) {
            LearningCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
