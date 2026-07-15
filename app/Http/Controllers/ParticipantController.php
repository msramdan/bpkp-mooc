<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ParticipantController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course enrollment manage'),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $courseId = trim((string) $request->query('course_id', ''));
        $status = trim((string) $request->query('status', ''));

        $enrollments = CourseEnrollment::query()
            ->with(['user:id,name,email', 'course:id,judul,kode,kategori,slug'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($courseId !== '', fn ($query) => $query->where('course_id', $courseId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('enrolled_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'peserta' => (int) User::role(Roles::PESERTA)->count(),
            'enrollments' => (int) CourseEnrollment::query()->count(),
            'courses' => (int) Course::query()->whereHas('enrollments')->count(),
            'avg_progress' => (int) round((float) CourseEnrollment::query()->avg('progress')),
        ];

        $courses = Course::query()
            ->orderBy('judul')
            ->get(['id', 'judul', 'kode']);

        $statuses = CourseEnrollment::query()
            ->select('status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('participants.index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            'courses' => $courses,
            'statuses' => $statuses,
            'filters' => [
                'q' => $search,
                'course_id' => $courseId,
                'status' => $status,
            ],
        ]);
    }
}
