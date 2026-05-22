<?php

namespace App\Http\Controllers;

use App\Http\Requests\Courses\StoreCourseEnrollmentRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
            new Middleware('permission:course enrollment manage'),
        ];
    }

    public function store(StoreCourseEnrollmentRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('manage', $course);

        $userId = (int) $request->validated('user_id');

        if ($course->enrollments()->where('user_id', $userId)->exists()) {
            return back()->with('error', __('Peserta sudah terdaftar di kursus ini.'));
        }

        DB::transaction(function () use ($course, $userId): void {
            CourseEnrollment::create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'progress' => 0,
                'modul_selesai' => 0,
                'status' => 'Berlangsung',
                'enrolled_at' => now(),
            ]);
        });

        return back()->with('success', __('Peserta berhasil didaftarkan ke kursus.'));
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
