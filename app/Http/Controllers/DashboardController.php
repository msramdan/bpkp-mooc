<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
        ];
    }

    public function index(): View
    {
        $totalCourses = Course::count();
        $published = Course::where('is_published', true)->count();
        $draft = max(0, $totalCourses - $published);
        $enrollments = CourseEnrollment::count();
        $peserta = User::role('peserta')->count();
        $certificates = Certificate::count();
        $berlangsung = CourseEnrollment::where('status', 'Berlangsung')->count();
        $selesai = CourseEnrollment::where('status', 'Selesai')->count();

        $topCourses = Course::query()
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get();

        $publishRate = $totalCourses > 0 ? (int) round(($published / $totalCourses) * 100) : 0;
        $completionRate = $enrollments > 0 ? (int) round(($selesai / $enrollments) * 100) : 0;

        return view('admin.dashboard', [
            'stats' => [
                'courses' => $totalCourses,
                'published' => $published,
                'draft' => $draft,
                'enrollments' => $enrollments,
                'peserta' => $peserta,
                'certificates' => $certificates,
            ],
            'publishRate' => $publishRate,
            'completionRate' => $completionRate,
            'recentCourses' => Course::query()
                ->withCount('enrollments')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'chartPublish' => [$published, $draft],
            'chartEnrollment' => [$berlangsung, $selesai],
            'chartTopCourses' => [
                'labels' => $topCourses->map(fn (Course $c) => Str::limit($c->judul, 22))->values()->all(),
                'series' => $topCourses->pluck('enrollments_count')->values()->all(),
            ],
        ]);
    }
}
