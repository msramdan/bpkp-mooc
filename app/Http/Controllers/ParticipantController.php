<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $letter = strtoupper(trim((string) $request->query('letter', '')));
        if (! preg_match('/^[A-Z]$/', $letter)) {
            $letter = '';
        }

        // List page: light query — only user fields + enrollment count (no nested enrollments).
        $participants = User::role(Roles::PESERTA)
            ->select(['id', 'name', 'email'])
            ->withCount('courseEnrollments')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($courseId !== '', function ($query) use ($courseId) {
                $query->whereExists(function ($exists) use ($courseId) {
                    $exists->select(DB::raw(1))
                        ->from('course_enrollments')
                        ->whereColumn('course_enrollments.user_id', 'users.id')
                        ->where('course_enrollments.course_id', $courseId);
                });
            })
            ->when($letter !== '', function ($query) use ($letter) {
                // Filter by first letter of the name (after leading trim).
                $query->whereRaw('UPPER(LEFT(TRIM(name), 1)) = ?', [$letter]);
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('participants.index', [
            'participants' => $participants,
            'courses' => $this->filterCourses(),
            'alphabet' => range('A', 'Z'),
            'filters' => [
                'q' => $search,
                'course_id' => $courseId,
                'letter' => $letter,
            ],
        ]);
    }

    /**
     * Lazy-load courses for one participant (modal). Avoids N×enrollments on the index page.
     */
    public function courses(User $user): JsonResponse
    {
        abort_unless($user->hasRole(Roles::PESERTA), 404);

        $items = $user->courseEnrollments()
            ->select(['id', 'user_id', 'course_id', 'progress', 'status', 'enrolled_at'])
            ->with(['course:id,judul,kode,kategori,slug'])
            ->latest('enrolled_at')
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                $course = $enrollment->course;
                if (! $course) {
                    return null;
                }

                return [
                    'title' => $course->judul,
                    'code' => $course->kode,
                    'category' => $course->kategori,
                    'status' => $enrollment->status ? __($enrollment->status) : '—',
                    'progress' => (int) $enrollment->progress,
                    'url' => route('courses.show', [$course, 'tab' => 'peserta']),
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'courses' => $items,
        ]);
    }

    public static function forgetCachedLists(): void
    {
        Cache::forget('admin.participants.filter-courses');
    }

    /** @return \Illuminate\Support\Collection<int, Course> */
    private function filterCourses()
    {
        return Cache::remember('admin.participants.filter-courses', now()->addMinutes(5), function () {
            return Course::query()
                ->select(['id', 'judul', 'kode'])
                ->whereExists(function ($exists) {
                    $exists->select(DB::raw(1))
                        ->from('course_enrollments')
                        ->whereColumn('course_enrollments.course_id', 'courses.id');
                })
                ->orderBy('judul')
                ->get();
        });
    }
}
