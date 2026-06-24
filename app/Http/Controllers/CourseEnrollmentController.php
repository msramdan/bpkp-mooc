<?php

namespace App\Http\Controllers;

use App\Support\Roles;
use App\Http\Requests\Courses\StoreCourseEnrollmentRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course enrollment manage'),
        ];
    }

    public function store(StoreCourseEnrollmentRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $userId = (int) $request->validated('user_id');
        $created = false;

        try {
            DB::transaction(function () use ($course, $userId, &$created): void {
                $existing = CourseEnrollment::query()
                    ->where('user_id', $userId)
                    ->where('course_id', $course->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return;
                }

                CourseEnrollment::query()->create([
                    'user_id' => $userId,
                    'course_id' => $course->id,
                    'progress' => 0,
                    'modul_selesai' => 0,
                    'status' => 'Berlangsung',
                    'enrolled_at' => now(),
                ]);

                $created = true;
            });
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', __('Peserta sudah terdaftar di kursus ini.'));
        }

        if ($created) {
            return back()->with('success', __('Peserta berhasil didaftarkan ke kursus.'));
        }

        return back()->with('error', __('Peserta sudah terdaftar di kursus ini.'));
    }

    public function destroy(Course $course, CourseEnrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        if ($enrollment->course_id !== $course->id) {
            abort(404);
        }

        $enrollment->delete();

        return back()->with('success', __('Pendaftaran peserta berhasil dihapus.'));
    }
}
