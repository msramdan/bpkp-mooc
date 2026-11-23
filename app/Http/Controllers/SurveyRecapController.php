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
        $active = $survey->responses()->whereNull('reset_at')
            ->get(['id', 'course_lesson_id', 'total_score', 'max_possible_score', 'essay_score', 'grading_status']);
        $courseRecap = $this->courseRecap($active);

        $overallStats = [
            'total_responses' => $active->count(),
            'average_pg' => $this->averagePg($active),
            'average_essay' => $this->averageEssay($active),
            'pending_essays' => $active->where('grading_status', 'pending_essay')->count(),
        ];

        return view('surveys.recap', compact('survey', 'responses', 'courseRecap', 'overallStats'));
    }

    public function gradeEssay(Request $request, SurveyResponse $response)
    {
        $validated = $request->validate([
            'scores' => 'required|array|max:200',
            'scores.*' => 'nullable|integer|min:0|max:100',
        ], [
            'scores.*.integer' => __('Nilai esai harus berupa angka bulat 0-100.'),
            'scores.*.min' => __('Nilai esai minimal 0.'),
            'scores.*.max' => __('Nilai esai maksimal 100.'),
        ]);

        // Kolom yang dikosongkan = belum ingin dinilai (bukan nilai 0).
        $validated['scores'] = array_filter($validated['scores'], fn ($v) => $v !== null && $v !== '');
        if ($validated['scores'] === []) {
            return back()->with('error', __('Isi minimal satu nilai esai terlebih dahulu.'));
        }

        $saved = DB::transaction(function () use ($validated, $response, $request): int {
            // Kunci jawaban ini agar dua admin yang menilai bersamaan tidak saling menimpa ringkasan.
            SurveyResponse::query()->whereKey($response->getKey())->lockForUpdate()->first();

            // Hanya jawaban ESAI milik response ini yang bisa dinilai (ID lain diabaikan).
            $answers = $response->answers()
                ->whereIn('id', array_map('strval', array_keys($validated['scores'])))
                ->whereHas('question', fn ($q) => $q->where('type', 'text'))
                ->get();

            foreach ($answers as $ans) {
                $ans->update([
                    'score' => (int) $validated['scores'][$ans->id],
                    'is_graded' => true,
                ]);
            }

            if ($answers->isNotEmpty()) {
                $response->forceFill(['essay_graded_at' => now(), 'essay_graded_by' => $request->user()->id])->save();
            }

            // Nilai esai dihitung terpisah dari nilai PG (lihat SurveyScoring).
            app(\App\Services\SurveyScoring::class)->recalculate($response->fresh());

            return $answers->count();
        });

        if ($saved === 0) {
            return back()->with('error', __('Tidak ada jawaban esai yang bisa dinilai pada lembar jawaban ini.'));
        }

        $fresh = $response->fresh();
        $message = $fresh->essay_score !== null
            ? __('Nilai esai disimpan. Nilai esai: :n (terpisah dari nilai PG).', ['n' => number_format((float) $fresh->essay_score, 1)])
            : __('Nilai esai disimpan. Masih ada esai yang belum dinilai.');

        return back()->with('success', $message);
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
                'Benar PG',
                'Jumlah Soal PG',
                'Nilai PG (0-100)',
                'Nilai Esai (0-100)',
                'Esai Dinilai',
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
                    $res->max_possible_score > 0 ? number_format($res->total_score / 100, 0) : '-',
                    $res->max_possible_score > 0 ? number_format($res->max_possible_score / 100, 0) : '-',
                    $res->max_possible_score > 0 ? number_format(\App\Services\SurveyScoring::percentage($res), 1, ',', '') : '-',
                    $res->essay_score !== null ? number_format((float) $res->essay_score, 1, ',', '') : '-',
                    $res->essay_answered_count > 0 ? $res->essay_graded_count . '/' . $res->essay_answered_count : '-',
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

        $courseRecap = $this->courseRecap($survey->responses()->whereNull('reset_at')->get());

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
                'Rata-Rata Nilai PG (0-100)',
                'Rata-Rata Nilai Esai (0-100)',
            ], ';');

            $i = 1;
            foreach ($courseRecap as $item) {
                fputcsv($handle, [
                    $i++,
                    $item['course_title'],
                    $item['total_participants'],
                    $item['average_pg'] !== null ? number_format($item['average_pg'], 1, ',', '') : '-',
                    $item['average_essay'] !== null ? number_format($item['average_essay'], 1, ',', '') : '-',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Rekap per kursus dari percobaan aktif. Nilai PG dan Nilai Esai dirata-rata TERPISAH.
     *
     * @param  \Illuminate\Support\Collection<int, SurveyResponse>  $responses
     */
    private function courseRecap($responses)
    {
        $responses->loadMissing('lesson:id,course_module_id', 'lesson.module:id,course_id', 'lesson.module.course:id,judul');

        return $responses
            ->groupBy(fn ($item) => $item->lesson?->module?->course?->id ?? 'no_course')
            ->map(fn ($items) => [
                'course_title' => $items->first()->lesson?->module?->course?->judul ?? 'Tanpa Kursus',
                'total_participants' => $items->count(),
                'average_pg' => $this->averagePg($items),
                'average_essay' => $this->averageEssay($items),
            ]);
    }

    /** Rata-rata Nilai PG (0-100) dari jawaban yang punya soal PG; null bila tidak ada. */
    private function averagePg($responses): ?float
    {
        $scored = $responses->filter(fn ($r) => $r->max_possible_score > 0);

        return $scored->isEmpty() ? null : round($scored->avg(fn ($r) => \App\Services\SurveyScoring::percentage($r)), 1);
    }

    /** Rata-rata Nilai Esai (0-100) dari jawaban yang esainya sudah dinilai semua; null bila tidak ada. */
    private function averageEssay($responses): ?float
    {
        $graded = $responses->filter(fn ($r) => $r->essay_score !== null);

        return $graded->isEmpty() ? null : round((float) $graded->avg(fn ($r) => (float) $r->essay_score), 1);
    }
}
