<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyRecapController extends Controller
{
    public function index(Survey $survey)
    {
        $responses = $survey->responses()
            ->with(['user', 'resetter:id,name', 'lesson.module.course', 'answers.question', 'answers.option'])
            ->latest()
            ->paginate(20);

        // Statistik hanya dari percobaan aktif (yang di-reset tidak dihitung).
        $courseRecap = $survey->responses()
            ->whereNull('reset_at')
            ->with('lesson.module.course')
            ->get()
            ->groupBy(function($item) {
                return $item->lesson?->module?->course?->id ?? 'no_course';
            })
            ->map(function ($items) {
                $firstCourse = $items->first()->lesson?->module?->course;
                return [
                    'course_title' => $firstCourse?->judul ?? 'Tanpa Kursus',
                    'total_participants' => $items->count(),
                    'average_score' => $items->avg('total_score'),
                    'max_possible' => $items->avg('max_possible_score'),
                ];
            });

        $overallStats = [
            'total_responses' => $survey->responses()->whereNull('reset_at')->count(),
            'average_score' => $survey->responses()->whereNull('reset_at')->avg('total_score') ?? 0,
            'pending_essays' => $survey->responses()->whereNull('reset_at')->where('grading_status', 'pending_essay')->count(),
        ];

        return view('surveys.recap', compact('survey', 'responses', 'courseRecap', 'overallStats'));
    }

    public function gradeEssay(Request $request, SurveyResponse $response)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $response) {
            foreach ($validated['scores'] as $answerId => $scoreVal) {
                $ans = $response->answers()->where('id', $answerId)->first();
                if ($ans && $ans->question->type === 'text') {
                    $ans->update([
                        'score' => (int) $scoreVal,
                        'is_graded' => true,
                    ]);
                }
            }

            // Nilai esai disimpan sebagai catatan, tidak ikut skor otomatis (lihat SurveyScoring).
            app(\App\Services\SurveyScoring::class)->recalculate($response);
        });

        return back()->with('success', __('Nilai esai berhasil disimpan. Esai tidak ikut dihitung dalam skor otomatis.'));
    }

    /** Reset semua percobaan aktif peserta pada materi dari response ini (ditandai, tidak dihapus). */
    public function resetAttempts(Request $request, SurveyResponse $response, \App\Services\SurveyAttemptService $attempts)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        if (! $response->lesson || ! $response->user) {
            return back()->with('error', __('Materi atau peserta untuk jawaban ini sudah tidak ada.'));
        }

        $count = $attempts->reset($response->user, $response->lesson, $request->user(), $data['reason'] ?? null);

        if ($count === 0) {
            return back()->with('error', __('Tidak ada percobaan aktif yang bisa di-reset (mungkin sudah di-reset admin lain).'));
        }

        return back()->with('success', __(':n percobaan :name pada ":lesson" di-reset. Peserta dapat mengerjakan ulang.', [
            'n' => $count,
            'name' => $response->user->name,
            'lesson' => $response->lesson->judul,
        ]));
    }

    public function exportParticipants(Survey $survey)
    {
        $fileName = 'rekap_nilai_peserta_' . str($survey->title)->slug() . '_' . date('Y-m-d_H-i') . '.csv';
        $responses = $survey->responses()->with(['user', 'lesson.module.course'])->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return new StreamedResponse(function () use ($responses) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nama Peserta',
                'Email Peserta',
                'Kursus / Materi',
                'Percobaan Ke',
                'Skor Diperoleh',
                'Skor Maksimal',
                'Status Penilaian',
                'Waktu Pengisian',
            ], ';');

            foreach ($responses as $index => $res) {
                fputcsv($handle, [
                    $index + 1,
                    $res->user?->name ?? '-',
                    $res->user?->email ?? '-',
                    ($res->lesson?->module?->course?->judul ?? '-') . ' (' . ($res->lesson?->judul ?? '-') . ')',
                    $res->attempt_number ?? 1,
                    number_format($res->total_score, 0),
                    number_format($res->max_possible_score, 0),
                    $res->reset_at ? 'Di-reset admin' : ($res->grading_status === 'pending_essay' ? 'Menunggu Penilaian Esai' : 'Selesai'),
                    $res->created_at?->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportCourses(Survey $survey)
    {
        $fileName = 'rekap_nilai_kursus_' . str($survey->title)->slug() . '_' . date('Y-m-d_H-i') . '.csv';

        $courseRecap = $survey->responses()
            ->with('lesson.module.course')
            ->get()
            ->groupBy(function($item) {
                return $item->lesson?->module?->course?->id ?? 'no_course';
            })
            ->map(function ($items) {
                $firstCourse = $items->first()->lesson?->module?->course;
                return [
                    'course_title' => $firstCourse?->judul ?? 'Tanpa Kursus',
                    'total_participants' => $items->count(),
                    'average_score' => $items->avg('total_score'),
                    'max_possible' => $items->avg('max_possible_score'),
                ];
            });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return new StreamedResponse(function () use ($courseRecap) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nama Kursus',
                'Jumlah Peserta Mengisi',
                'Rata-Rata Skor Diperoleh',
                'Rata-Rata Skor Maksimal',
            ], ';');

            $i = 1;
            foreach ($courseRecap as $item) {
                fputcsv($handle, [
                    $i++,
                    $item['course_title'],
                    $item['total_participants'],
                    number_format($item['average_score'], 2),
                    number_format($item['max_possible'], 2),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
