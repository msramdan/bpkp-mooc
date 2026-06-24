<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\LearningProgressService;
use App\Support\PesertaAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CatalogEnrollmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:peserta'),
        ];
    }

    public function store(Course $course, LearningProgressService $progress): RedirectResponse
    {
        if (! $course->is_published) {
            return back()->with('error', __('Kursus ini belum dipublikasikan.'));
        }

        $existing = PesertaAccess::user()
            ->courseEnrollments()
            ->where('course_id', $course->id)
            ->exists();

        if ($existing) {
            return to_route('peserta.kursus.show', $course)
                ->with('success', __('Anda sudah terdaftar di kursus ini.'));
        }

        $progress->enroll(PesertaAccess::user(), $course);

        return to_route('peserta.kursus.show', $course)
            ->with('success', __('Berhasil mendaftar ke kursus.'));
    }
}
