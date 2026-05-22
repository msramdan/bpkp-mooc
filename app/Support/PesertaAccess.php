<?php

namespace App\Support;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class PesertaAccess
{
    public static function user(): User
    {
        return auth()->user();
    }

    public static function enrollmentForCourse(Course $course): CourseEnrollment
    {
        $enrollment = static::user()
            ->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            throw new AuthorizationException(__('Anda tidak terdaftar pada kursus ini.'));
        }

        if (! $course->is_published) {
            throw new AuthorizationException(__('Kursus ini belum dipublikasikan.'));
        }

        return $enrollment;
    }

    /**
     * @return Collection<int, string>
     */
    public static function enrolledCourseTitles(): Collection
    {
        return static::user()
            ->courseEnrollments()
            ->with('course:id,judul')
            ->get()
            ->pluck('course.judul')
            ->filter();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function filterDummyByEnrolledCourses(array $items, string $courseKey = 'kursus'): array
    {
        $titles = static::enrolledCourseTitles();

        if ($titles->isEmpty()) {
            return [];
        }

        return array_values(array_filter(
            $items,
            fn (array $item): bool => $titles->contains($item[$courseKey] ?? '')
        ));
    }
}
