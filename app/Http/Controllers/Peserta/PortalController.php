<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CertificateService;
use App\Services\LearningProgressService;
use App\Support\PesertaAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PortalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:peserta'),
        ];
    }

    public function dashboard(): View
    {
        $allEnrollments = PesertaAccess::user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('updated_at')
            ->get();

        $kursus = $allEnrollments->take(4);
        $user = PesertaAccess::user();

        $stats = [
            'kursus_aktif' => $allEnrollments->where('status', 'Berlangsung')->count(),
            'kursus_selesai' => $allEnrollments->where('status', 'Selesai')->count(),
            'sertifikat' => $user->certificates()->count(),
            'terdaftar' => $allEnrollments->count(),
        ];

        $lanjutkan = $allEnrollments->firstWhere('status', 'Berlangsung')
            ?? $allEnrollments->first();

        $chartProgress = [
            'labels' => $allEnrollments->map(fn ($e) => \Illuminate\Support\Str::limit($e->course->judul, 22))->values()->all(),
            'series' => $allEnrollments->pluck('progress')->values()->all(),
        ];

        return view('peserta.dashboard', [
            'stats' => $stats,
            'kursus' => $kursus,
            'lanjutkan' => $lanjutkan,
            'sertifikatTerbaru' => $user->certificates()
                ->with('course')
                ->orderByDesc('issued_at')
                ->take(4)
                ->get(),
            'rata_progress' => (int) round($allEnrollments->avg('progress') ?: 0),
            'chartProgress' => $chartProgress,
            'chartStatus' => [
                (int) $stats['kursus_aktif'],
                (int) $stats['kursus_selesai'],
            ],
        ]);
    }

    public function kursus(): View
    {
        $enrollments = PesertaAccess::user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('updated_at')
            ->get();

        return view('peserta.kursus.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function kursusShow(Course $course, LearningProgressService $progress): View
    {
        $this->authorize('view', $course);

        $user = PesertaAccess::user();
        $enrollment = PesertaAccess::enrollmentForCourse($course);

        $course->load([
            'modules' => fn ($query) => $query->orderBy('urutan')->with([
                'lessons' => fn ($q) => $q->orderBy('urutan'),
            ]),
        ]);

        $completedIds = $progress->completedLessonIds($user, $course);
        $ordered = $progress->orderedLessons($course);
        $totalLessons = $ordered->count();
        $moduleCount = $course->modules->count();
        $completedModules = min($enrollment->modul_selesai, $moduleCount);

        $activeModule = $course->modules->first(function ($module) use ($progress, $user, $course, $completedIds) {
            return $module->lessons->contains(
                fn ($lesson) => $progress->isLessonAccessible($user, $course, $lesson, $completedIds)
                    && ! $completedIds->contains($lesson->id)
            );
        }) ?? $course->modules->last();

        return view('peserta.kursus.show', [
            'course' => $course,
            'enrollment' => $enrollment,
            'totalLessons' => $totalLessons,
            'completedModules' => $completedModules,
            'moduleCount' => $moduleCount,
            'activeModule' => $activeModule,
            'completedIds' => $completedIds,
            'progressService' => $progress,
        ]);
    }

    public function katalog(): View
    {
        $enrolledIds = PesertaAccess::user()->courseEnrollments()->pluck('course_id');

        $courses = Course::query()
            ->where('is_published', true)
            ->withCount('enrollments')
            ->orderByDesc('rating')
            ->orderBy('judul')
            ->get();

        return view('peserta.katalog.index', [
            'courses' => $courses,
            'enrolledIds' => $enrolledIds,
            'kategoris' => $courses->pluck('kategori')->unique()->sort()->values(),
        ]);
    }

    public function progres(): View
    {
        $enrollments = PesertaAccess::user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('progress')
            ->get();

        return view('peserta.progres.index', [
            'enrollments' => $enrollments,
            'rata_progress' => (int) round($enrollments->avg('progress') ?: 0),
            'berlangsung' => $enrollments->where('status', 'Berlangsung')->count(),
            'selesai' => $enrollments->where('status', 'Selesai')->count(),
        ]);
    }

    public function sertifikat(CertificateService $certificateService): View
    {
        $user = PesertaAccess::user();

        $enrollments = $user->courseEnrollments()
            ->with('course')
            ->get();

        foreach ($enrollments->where('progress', '>=', 100) as $enrollment) {
            $certificateService->issueForCompletedEnrollment($user, $enrollment->course, $enrollment);
        }

        $issued = $user->certificates()
            ->with('course')
            ->orderByDesc('issued_at')
            ->get();

        $issuedCourseIds = $issued->pluck('course_id');

        $pending = $enrollments
            ->filter(fn ($e) => ! $issuedCourseIds->contains($e->course_id))
            ->map(fn ($enrollment) => [
                'kursus' => $enrollment->course->judul,
                'kode' => $enrollment->course->kode,
                'thumbnail' => $enrollment->course->thumbnail_url,
                'status' => $enrollment->progress >= 100 ? 'Menunggu penerbitan' : 'Berlangsung',
                'nomor' => null,
                'tanggal' => '—',
                'nilai' => $enrollment->progress >= 100 ? $enrollment->progress.'%' : '—',
                'is_terbit' => false,
                'course' => $enrollment->course,
            ]);

        $items = $issued->map(fn ($certificate) => [
            'certificate' => $certificate,
            'kursus' => $certificate->course->judul,
            'kode' => $certificate->course->kode,
            'thumbnail' => $certificate->course->thumbnail_url,
            'status' => 'Terbit',
            'nomor' => $certificate->nomor,
            'tanggal' => $certificate->issued_at?->format('d M Y'),
            'nilai' => $certificate->nilai_akhir.'%',
            'is_terbit' => true,
        ])->concat($pending);

        return view('peserta.sertifikat.index', [
            'items' => $items,
            'stats' => [
                'terbit' => $issued->count(),
                'menunggu' => $pending->count(),
            ],
        ]);
    }
}
