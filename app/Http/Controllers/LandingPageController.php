<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $publishedQuery = Course::query()
            ->where('is_published', true)
            ->withCount('enrollments');

        $featuredCourses = (clone $publishedQuery)
            ->orderByDesc('enrollments_count')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $highlightCourse = $featuredCourses->first();

        $categories = Course::query()
            ->where('is_published', true)
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori', DB::raw('count(*) as courses_count'))
            ->groupBy('kategori')
            ->orderByDesc('courses_count')
            ->take(8)
            ->get();

        $stats = [
            'courses' => (clone $publishedQuery)->count(),
            'participants' => User::role(Roles::PESERTA)->count(),
            'enrollments' => CourseEnrollment::query()->count(),
            'certificates' => Certificate::query()->count(),
        ];

        return view('frontend.index', [
            'featuredCourses' => $featuredCourses,
            'highlightCourse' => $highlightCourse,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
