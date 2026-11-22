<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\Exceptions\SurveyAttemptException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aturan percobaan kuesioner / pre test / post test.
 *
 * - Pre test: selalu 1 kali.
 * - Post test & kuesioner: sesuai "Maksimal percobaan" di materi (default 1).
 * - Nilai resmi: tertinggi (default) atau terakhir, hanya dari percobaan aktif.
 * - Reset admin menandai percobaan (reset_at), tidak menghapus: jejak audit tetap ada.
 *
 * Race condition: pengiriman dikunci per peserta-kursus (SELECT ... FOR UPDATE pada
 * baris enrollment) + unique index (lesson, user, attempt_number) sebagai penjaga terakhir.
 */
class SurveyAttemptService
{
    public const MODES = [
        'kuesioner' => 'Kuesioner / Kuis',
        'pre_test' => 'Pre Test',
        'post_test' => 'Post Test',
    ];

    public const POLICIES = [
        'highest' => 'Nilai tertinggi',
        'latest' => 'Nilai terakhir',
    ];

    public const MAX_ATTEMPTS_LIMIT = 20;

    public function mode(CourseLesson $lesson): string
    {
        return array_key_exists((string) $lesson->survey_mode, self::MODES) ? (string) $lesson->survey_mode : 'kuesioner';
    }

    public function maxAttempts(CourseLesson $lesson): int
    {
        if ($this->mode($lesson) === 'pre_test') {
            return 1;
        }

        return max(1, min(self::MAX_ATTEMPTS_LIMIT, (int) ($lesson->max_attempts ?: 1)));
    }

    public function policy(CourseLesson $lesson): string
    {
        return array_key_exists((string) $lesson->attempt_score_policy, self::POLICIES) ? (string) $lesson->attempt_score_policy : 'highest';
    }

    /** @return Collection<int, SurveyResponse> percobaan aktif, urut dari yang pertama. */
    public function activeAttempts(User $user, CourseLesson $lesson): Collection
    {
        return SurveyResponse::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $lesson->id)
            ->active()
            ->orderBy('attempt_number')
            ->get();
    }

    public function remaining(User $user, CourseLesson $lesson, ?Collection $active = null): int
    {
        $active ??= $this->activeAttempts($user, $lesson);

        return max(0, $this->maxAttempts($lesson) - $active->count());
    }

    /** Percobaan yang nilainya dipakai (sesuai kebijakan). */
    public function officialResponse(User $user, CourseLesson $lesson, ?Collection $active = null): ?SurveyResponse
    {
        $active ??= $this->activeAttempts($user, $lesson);

        return $this->pickOfficial($active, $this->policy($lesson));
    }

    /** @param  Collection<int, SurveyResponse>  $attempts */
    public function pickOfficial(Collection $attempts, string $policy = 'highest'): ?SurveyResponse
    {
        $attempts = $attempts->filter(fn (SurveyResponse $r) => ! $r->isReset())->values();

        if ($attempts->isEmpty()) {
            return null;
        }

        if ($policy === 'latest') {
            return $attempts->sortByDesc('attempt_number')->first();
        }

        // Tertinggi; jika sama, ambil yang lebih akhir.
        return $attempts->sortBy([
            fn ($a, $b) => SurveyScoring::percentage($b) <=> SurveyScoring::percentage($a),
            fn ($a, $b) => $b->attempt_number <=> $a->attempt_number,
        ])->first();
    }

    /**
     * Simpan satu percobaan baru secara atomik.
     *
     * @param  callable(SurveyResponse): void  $fillAnswers  mengisi jawaban & menghitung skor
     *
     * @throws SurveyAttemptException
     */
    public function submit(User $user, Course $course, CourseLesson $lesson, callable $fillAnswers): SurveyResponse
    {
        try {
            return DB::transaction(function () use ($user, $course, $lesson, $fillAnswers): SurveyResponse {
                // Kunci per peserta-kursus: pengiriman paralel antre di sini.
                CourseEnrollment::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->lockForUpdate()
                    ->first();

                $all = SurveyResponse::query()
                    ->where('user_id', $user->id)
                    ->where('course_lesson_id', $lesson->id)
                    ->get(['id', 'attempt_number', 'reset_at']);

                $activeCount = $all->whereNull('reset_at')->count();
                if ($activeCount >= $this->maxAttempts($lesson)) {
                    throw new SurveyAttemptException('limit_reached', $this->maxAttempts($lesson) === 1
                        ? __('Anda sudah mengirim jawaban untuk aktivitas ini.')
                        : __('Batas percobaan (:n kali) sudah habis.', ['n' => $this->maxAttempts($lesson)]));
                }

                $response = SurveyResponse::query()->create([
                    'survey_id' => $lesson->survey_id,
                    'user_id' => $user->id,
                    'course_lesson_id' => $lesson->id,
                    'attempt_number' => ((int) $all->max('attempt_number')) + 1,
                ]);

                $fillAnswers($response);

                return $response->fresh();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw new SurveyAttemptException('conflict', __('Jawaban sedang diproses. Muat ulang halaman untuk melihat hasilnya.'));
        }
    }

    /**
     * Reset semua percobaan aktif seorang peserta pada satu materi.
     * Mengembalikan jumlah percobaan yang di-reset.
     */
    public function reset(User $peserta, CourseLesson $lesson, User $admin, ?string $reason = null): int
    {
        return DB::transaction(function () use ($peserta, $lesson, $admin, $reason): int {
            return SurveyResponse::query()
                ->where('user_id', $peserta->id)
                ->where('course_lesson_id', $lesson->id)
                ->active()
                ->lockForUpdate()
                ->update([
                    'reset_at' => now(),
                    'reset_by' => $admin->id,
                    'reset_reason' => $reason !== null && trim($reason) !== '' ? mb_substr(trim($reason), 0, 255) : null,
                ]);
        });
    }

    /**
     * Nilai resmi per materi untuk sekumpulan survey (dipakai gerbang sertifikat).
     *
     * @param  array<int, string>  $surveyIds
     * @return Collection<string, SurveyResponse> key: course_lesson_id (atau survey_id bila tanpa materi)
     */
    public function officialBySurveys(User $user, array $surveyIds): Collection
    {
        if ($surveyIds === []) {
            return collect();
        }

        $responses = SurveyResponse::query()
            ->where('user_id', $user->id)
            ->whereIn('survey_id', $surveyIds)
            ->active()
            ->with('lesson:id,attempt_score_policy')
            ->get();

        return $responses
            ->groupBy(fn (SurveyResponse $r) => $r->course_lesson_id ?? 'survey:'.$r->survey_id)
            ->map(fn (Collection $group) => $this->pickOfficial($group, $group->first()->lesson?->attempt_score_policy ?? 'highest'))
            ->filter();
    }
}
