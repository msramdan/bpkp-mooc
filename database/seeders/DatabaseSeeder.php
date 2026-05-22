<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(class: [
            UserSeeder::class,
            RoleAndPermissionSeeder::class,
            PesertaSeeder::class,
            LearningCategorySeeder::class,
            LearningTagSeeder::class,
            CourseSeeder::class,
            CourseContentSeeder::class,
            CourseEnrollmentSeeder::class,
        ]);
    }
}
