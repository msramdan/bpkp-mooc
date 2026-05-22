<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('course view');
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->can('course view')) {
            return true;
        }

        if ($user->hasRole('peserta') && $user->can('peserta kursus view')) {
            return $user->courseEnrollments()
                ->where('course_id', $course->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('course create');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('course edit');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('course delete');
    }
}
