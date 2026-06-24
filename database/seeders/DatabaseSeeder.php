<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            SuperAdminSeeder::class,
        ]);

        if (filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call([
                PesertaSeeder::class,
                LearningCategorySeeder::class,
                LearningTagSeeder::class,
                CourseSeeder::class,
                CourseContentSeeder::class,
                CourseEnrollmentSeeder::class,
            ]);
        }
    }
}
