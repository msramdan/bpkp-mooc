<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use App\Services\LearningProgressService;
use App\Support\PesertaAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CertificateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:peserta'),
        ];
    }

    public function show(Certificate $certificate): View
    {
        $this->authorize('view', $certificate);

        $certificate->load(['course', 'user']);

        return view('peserta.sertifikat.show', [
            'certificate' => $certificate,
        ]);
    }

    public function issueFromEnrollment(
        Course $course,
        CertificateService $certificates,
    ): RedirectResponse {
        $this->authorize('view', $course);

        $enrollment = PesertaAccess::enrollmentForCourse($course);

        if ($enrollment->progress < 100) {
            return back()->with('error', __('Kursus belum selesai. Selesaikan semua materi wajib terlebih dahulu.'));
        }

        $certificates->issueForCompletedEnrollment(
            PesertaAccess::user(),
            $course,
            $enrollment
        );

        return to_route('peserta.sertifikat.index')
            ->with('success', __('Sertifikat berhasil diterbitkan.'));
    }
}
