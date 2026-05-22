<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentPolicy
{
    public function manage(User $user, Course $course): bool
    {
        return $user->can('course enrollment manage');
    }

    public function delete(User $user, CourseEnrollment $enrollment): bool
    {
        return $user->can('course enrollment manage');
    }
}
