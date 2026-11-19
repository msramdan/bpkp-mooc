<?php

namespace App\Services;

use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;

/**
 * Hitung skor otomatis sebuah jawaban kuis/survei.
 *
 * Aturan (permintaan user, Nov 2026):
 * - Yang dinilai otomatis hanya soal pilihan ganda (radio) dan checklist (checkbox)
 *   yang punya kunci jawaban. Essay, rating/survei, dan pilihan tanpa kunci diabaikan.
 * - Pilihan ganda benar jika opsi yang dipilih adalah kunci.
 * - Checklist benar jika opsi yang dicentang persis sama dengan seluruh kunci.
 * - Skor akhir = jumlah soal benar / jumlah soal yang dinilai x 100.
 *   total_score menyimpan (jumlah benar x 100), max_possible_score (jumlah soal dinilai x 100).
 */
class SurveyScoring
{
    public static function correctOptionIds(SurveyQuestion $question): array
    {
        return $question->options
            ->filter(fn ($o) => $o->is_correct || ($question->type === 'radio' && $o->score_value >= 100))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public static function isGradable(SurveyQuestion $question): bool
    {
        return in_array($question->type, ['radio', 'checkbox'], true)
            && self::correctOptionIds($question) !== [];
    }

    public function recalculate(SurveyResponse $response): SurveyResponse
    {
        $response->loadMissing(['survey.questions.options', 'answers']);
        $answersByQuestion = $response->answers->groupBy('survey_question_id');

        $correct = 0;
        $gradable = 0;

        foreach ($response->survey->questions as $question) {
            if (! self::isGradable($question)) {
                continue;
            }

            $gradable++;
            $keys = self::correctOptionIds($question);
            $chosen = ($answersByQuestion->get($question->id) ?? collect())
                ->pluck('survey_option_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->sort()
                ->values()
                ->all();

            $isCorrect = $question->type === 'radio'
                ? count($chosen) === 1 && in_array($chosen[0], $keys, true)
                : $chosen !== [] && $chosen == collect($keys)->sort()->values()->all();

            if ($isCorrect) {
                $correct++;
            }
        }

        $response->update([
            'total_score' => $correct * 100,
            'max_possible_score' => $gradable * 100,
            'grading_status' => 'completed',
        ]);

        return $response;
    }

    /**
     * Nilai 0-100 dari sebuah response.
     */
    public static function percentage(SurveyResponse $response): float
    {
        return $response->max_possible_score > 0
            ? round(($response->total_score / $response->max_possible_score) * 100, 1)
            : 0.0;
    }
}
