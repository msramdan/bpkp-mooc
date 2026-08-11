<?php

namespace App\Http\Controllers;

use App\Support\Roles;
use App\Http\Requests\Courses\BulkDestroyCourseEnrollmentRequest;
use App\Http\Requests\Courses\StoreCourseEnrollmentRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\LearningProgressService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseEnrollmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course enrollment manage'),
        ];
    }

    public function progress(
        Course $course,
        CourseEnrollment $enrollment,
        LearningProgressService $progress
    ): JsonResponse {
        $this->authorize('view', $course);

        if ($enrollment->course_id !== $course->id) {
            abort(404);
        }

        $enrollment->loadMissing('user:id,name,email');

        if (! $enrollment->user) {
            abort(404);
        }

        return response()->json(
            $progress->progressBreakdown($enrollment->user, $course, $enrollment)
        );
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

    public function downloadTemplate(Course $course): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('manageEnrollments', $course);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CourseEnrollmentExport(
                [
                    ['199004212012101001'],
                    ['198507152010011005'],
                ],
                ['NIP']
            ),
            'template-import-peserta-'.Str::slug($course->title).'.xlsx'
        );
    }

    public function export(Course $course): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('manageEnrollments', $course);

        $rows = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->with('user:id,name,nip,email,pangkat_golongan,jabatan,unit_organisasi')
            ->orderBy('enrolled_at')
            ->get()
            ->map(fn (CourseEnrollment $e) => [
                $e->user->nip ?? '-',
                $e->user->name,
                $e->user->email,
                $e->user->pangkat_golongan ?? '-',
                $e->user->jabatan ?? '-',
                $e->user->unit_organisasi ?? '-',
                $e->enrolled_at->translatedFormat('d F Y H:i'),
                $e->progress.'%',
                $e->status,
            ])
            ->all();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CourseEnrollmentExport(
                $rows,
                ['NIP', 'Nama', 'Email', 'Pangkat/Golongan', 'Jabatan', 'Unit Organisasi', 'Tanggal Terdaftar', 'Progres', 'Status']
            ),
            'peserta-'.Str::slug($course->title).'-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function import(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [], [
            'import_file' => __('File import'),
        ]);

        $import = new \App\Imports\CourseEnrollmentImport();
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('import_file'));
        $nips = $import->nips->unique()->values();

        if ($nips->isEmpty()) {
            return redirect()
                ->route('courses.show', [$course, 'tab' => 'peserta'])
                ->with('error', __('Tidak ada data NIP yang bisa diimpor.'));
        }

        $enrolled = 0;
        $alreadyEnrolled = 0;
        $notFound = [];

        DB::transaction(function () use ($course, $nips, &$enrolled, &$alreadyEnrolled, &$notFound): void {
            foreach ($nips as $nip) {
                $user = User::role(Roles::PESERTA)
                    ->where('nip', $nip)
                    ->first();

                if (! $user) {
                    $notFound[] = $nip;
                    continue;
                }

                $exists = CourseEnrollment::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ($exists) {
                    $alreadyEnrolled++;
                    continue;
                }

                CourseEnrollment::query()->create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'progress' => 0,
                    'modul_selesai' => 0,
                    'status' => 'Berlangsung',
                    'enrolled_at' => now(),
                ]);

                $enrolled++;
            }
        });

        if ($enrolled > 0) {
            UserController::forgetCachedLists();
        }

        $messages = [];
        if ($enrolled > 0) {
            $messages[] = __(':count peserta berhasil didaftarkan.', ['count' => $enrolled]);
        }
        if ($alreadyEnrolled > 0) {
            $messages[] = __(':count sudah terdaftar sebelumnya.', ['count' => $alreadyEnrolled]);
        }
        if (count($notFound) > 0) {
            $messages[] = __(':count NIP tidak ditemukan: :nips', [
                'count' => count($notFound),
                'nips' => implode(', ', array_slice($notFound, 0, 10)).(count($notFound) > 10 ? '...' : ''),
            ]);
        }

        $flashKey = $enrolled > 0 ? 'success' : ($notFound ? 'error' : 'info');

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'peserta'])
            ->with($flashKey, implode(' ', $messages));
    }

}
