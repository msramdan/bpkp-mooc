<?php

namespace App\Http\Controllers;

use App\Support\Roles;
use App\Http\Requests\Courses\BulkDestroyCourseEnrollmentRequest;
use App\Http\Requests\Courses\StoreCourseEnrollmentRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function availableParticipants(Request $request, Course $course): JsonResponse
    {
        $this->authorize('manageEnrollments', $course);

        $q = trim((string) $request->query('q', ''));
        $like = $q !== ''
            ? '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%'
            : null;

        $users = User::role(Roles::PESERTA)
            ->select(['id', 'name', 'email'])
            ->whereNotExists(function ($exists) use ($course) {
                $exists->select(DB::raw(1))
                    ->from('course_enrollments')
                    ->whereColumn('course_enrollments.user_id', 'users.id')
                    ->where('course_enrollments.course_id', $course->id);
            })
            ->when($like !== null, function ($query) use ($like) {
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn (User $user) => [
                'value' => (string) $user->id,
                'label' => $user->name.' ('.$user->email.')',
            ])
            ->values();

        return response()->json(['results' => $users]);
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
            return redirect()
                ->route('courses.show', [$course, 'tab' => 'peserta'])
                ->with('error', __('Peserta sudah terdaftar di kursus ini.'));
        }

        if ($created) {
            UserController::forgetCachedLists();

            return redirect()
                ->route('courses.show', [$course, 'tab' => 'peserta'])
                ->with('success', __('Peserta berhasil didaftarkan ke kursus.'));
        }

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'peserta'])
            ->with('error', __('Peserta sudah terdaftar di kursus ini.'));
    }

    public function destroy(Course $course, CourseEnrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        if ($enrollment->course_id !== $course->id) {
            abort(404);
        }

        $enrollment->delete();
        UserController::forgetCachedLists();

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'peserta'])
            ->with('success', __('Pendaftaran peserta berhasil dihapus.'));
    }

    public function bulkDestroy(BulkDestroyCourseEnrollmentRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $ids = array_values(array_unique($request->validated('enrollment_ids')));

        $deleted = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->whereIn('id', $ids)
            ->delete();

        if ($deleted === 0) {
            return redirect()
                ->route('courses.show', [$course, 'tab' => 'peserta'])
                ->with('error', __('Tidak ada pendaftaran yang dihapus.'));
        }

        UserController::forgetCachedLists();

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'peserta'])
            ->with('success', __(':count pendaftaran peserta berhasil dihapus.', ['count' => $deleted]));
    }
}
