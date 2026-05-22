<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
        ];
    }

    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'courses' => Course::count(),
                'published' => Course::where('is_published', true)->count(),
                'enrollments' => CourseEnrollment::count(),
                'peserta' => User::role('peserta')->count(),
            ],
            'recentCourses' => Course::query()
                ->withCount('enrollments')
                ->latest('updated_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
