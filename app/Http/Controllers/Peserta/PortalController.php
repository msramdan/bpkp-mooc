<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        $kursus = $allEnrollments->take(3);
        $tugas = PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.tugas', []));
        $ujian = PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.ujian', []));

        $stats = [
            'kursus_aktif' => $allEnrollments->where('status', 'Berlangsung')->count(),
            'kursus_selesai' => $allEnrollments->where('status', 'Selesai')->count(),
            'tugas_pending' => collect($tugas)->where('status', 'Belum dikumpulkan')->count(),
            'ujian_mendatang' => collect($ujian)->whereIn('status', ['Terjadwal', 'Bisa dikerjakan'])->count(),
        ];

        $chartProgress = [
            'labels' => $allEnrollments->map(fn ($e) => \Illuminate\Support\Str::limit($e->course->judul, 22))->values()->all(),
            'series' => $allEnrollments->pluck('progress')->values()->all(),
        ];

        return view('peserta.dashboard', [
            'stats' => $stats,
            'kursus' => $kursus,
            'jadwal' => array_slice(
                PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.jadwal', [])),
                0,
                4
            ),
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

    public function kursusShow(Course $course): View
    {
        $this->authorize('view', $course);

        $enrollment = PesertaAccess::enrollmentForCourse($course);

        $course->load([
            'modules' => fn ($query) => $query->orderBy('urutan')->with([
                'lessons' => fn ($q) => $q->orderBy('urutan'),
            ]),
        ]);

        $totalLessons = $course->modules->sum(fn ($m) => $m->lessons->count());
        $moduleCount = $course->modules->count();
        $completedModules = min($enrollment->modul_selesai, $moduleCount);
        $activeModule = $course->modules->firstWhere('urutan', $completedModules + 1)
            ?? $course->modules->last();

        return view('peserta.kursus.show', [
            'course' => $course,
            'enrollment' => $enrollment,
            'totalLessons' => $totalLessons,
            'completedModules' => $completedModules,
            'moduleCount' => $moduleCount,
            'activeModule' => $activeModule,
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

    public function tugas(): View
    {
        return view('peserta.tugas.index', [
            'items' => PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.tugas', [])),
        ]);
    }

    public function ujian(): View
    {
        return view('peserta.ujian.index', [
            'items' => PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.ujian', [])),
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

    public function sertifikat(): View
    {
        $items = collect(PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.sertifikat', [])))
            ->map(function (array $item): array {
                $course = Course::query()->where('judul', $item['kursus'])->first();
                $item['thumbnail'] = $course?->thumbnail;
                $item['kode'] = $course?->kode;
                $item['is_terbit'] = $item['status'] === 'Terbit';

                return $item;
            });

        return view('peserta.sertifikat.index', [
            'items' => $items,
            'stats' => [
                'terbit' => $items->where('is_terbit', true)->count(),
                'menunggu' => $items->where('is_terbit', false)->count(),
            ],
        ]);
    }

    public function jadwal(): View
    {
        return view('peserta.jadwal.index', [
            'items' => PesertaAccess::filterDummyByEnrolledCourses(config('peserta.dummy.jadwal', [])),
        ]);
    }
}
