<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    /**
     * Enrollment template per peserta (same demo data for all).
     *
     * @var array<int, array<string, mixed>>
     */
    private array $templates = [
        ['kode' => 'AUD-101', 'progress' => 72, 'modul_selesai' => 8, 'status' => 'Berlangsung', 'deadline' => '2026-06-15'],
        ['kode' => 'MR-205', 'progress' => 45, 'modul_selesai' => 5, 'status' => 'Berlangsung', 'deadline' => '2026-07-01'],
        ['kode' => 'AK-088', 'progress' => 100, 'modul_selesai' => 6, 'status' => 'Selesai', 'deadline' => '2026-04-30'],
    ];

    public function run(): void
    {
        $courses = Course::query()->whereIn('kode', collect($this->templates)->pluck('kode'))->get()->keyBy('kode');

        User::role('peserta')->each(function (User $user) use ($courses): void {
            foreach ($this->templates as $template) {
                $course = $courses->get($template['kode']);
                if (! $course) {
                    continue;
                }

                CourseEnrollment::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'progress' => $template['progress'],
                        'modul_selesai' => $template['modul_selesai'],
                        'status' => $template['status'],
                        'deadline' => $template['deadline'],
                        'enrolled_at' => now()->subDays(rand(14, 90)),
                    ]
                );
            }
        });
    }
}
