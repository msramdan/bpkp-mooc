<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
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
        $kursus = auth()->user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get();
        $tugas = config('peserta.dummy.tugas', []);
        $ujian = config('peserta.dummy.ujian', []);

        $allEnrollments = auth()->user()->courseEnrollments()->get();

        $stats = [
            'kursus_aktif' => $allEnrollments->where('status', 'Berlangsung')->count(),
            'kursus_selesai' => $allEnrollments->where('status', 'Selesai')->count(),
            'tugas_pending' => collect($tugas)->where('status', 'Belum dikumpulkan')->count(),
            'ujian_mendatang' => collect($ujian)->whereIn('status', ['Terjadwal', 'Bisa dikerjakan'])->count(),
        ];

        return view('peserta.dashboard', [
            'stats' => $stats,
            'kursus' => $kursus,
            'jadwal' => array_slice(config('peserta.dummy.jadwal', []), 0, 3),
        ]);
    }

    public function kursus(): View
    {
        $enrollments = auth()->user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('updated_at')
            ->get();

        return view('peserta.kursus.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function katalog(): View
    {
        $enrolledIds = auth()->user()->courseEnrollments()->pluck('course_id');

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
            'items' => config('peserta.dummy.tugas', []),
        ]);
    }

    public function ujian(): View
    {
        return view('peserta.ujian.index', [
            'items' => config('peserta.dummy.ujian', []),
        ]);
    }

    public function progres(): View
    {
        $enrollments = auth()->user()
            ->courseEnrollments()
            ->with('course')
            ->orderByDesc('progress')
            ->get();

        return view('peserta.progres.index', [
            'enrollments' => $enrollments,
            'rata_progress' => (int) round($enrollments->avg('progress') ?: 0),
        ]);
    }

    public function sertifikat(): View
    {
        return view('peserta.sertifikat.index', [
            'items' => config('peserta.dummy.sertifikat', []),
        ]);
    }

    public function jadwal(): View
    {
        return view('peserta.jadwal.index', [
            'items' => config('peserta.dummy.jadwal', []),
        ]);
    }
}
