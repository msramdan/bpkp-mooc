<?php

namespace Tests\Unit;

use App\Models\CourseLesson;
use App\Models\SurveyResponse;
use App\Services\SurveyAttemptService;
use Illuminate\Support\Carbon;
use Tests\TestCase; // app di-boot untuk cast tanggal Eloquent (tanpa menulis ke DB)

class SurveyAttemptPolicyTest extends TestCase
{
    private function attempt(int $n, float $score, float $max = 200, bool $reset = false): SurveyResponse
    {
        $r = new SurveyResponse;
        $r->setRawAttributes([
            'id' => 'r'.$n,
            'attempt_number' => $n,
            'total_score' => $score,
            'max_possible_score' => $max,
            'reset_at' => $reset ? Carbon::now()->toDateTimeString() : null,
        ]);

        return $r;
    }

    private function service(): SurveyAttemptService
    {
        return new SurveyAttemptService;
    }

    public function test_highest_picks_best_score_and_latest_on_tie(): void
    {
        $attempts = collect([$this->attempt(1, 100), $this->attempt(2, 200), $this->attempt(3, 200), $this->attempt(4, 0)]);

        $this->assertSame(3, $this->service()->pickOfficial($attempts, 'highest')->attempt_number);
    }

    public function test_latest_picks_last_attempt_even_if_lower(): void
    {
        $attempts = collect([$this->attempt(1, 200), $this->attempt(2, 0)]);

        $this->assertSame(2, $this->service()->pickOfficial($attempts, 'latest')->attempt_number);
    }

    public function test_reset_attempts_are_never_official(): void
    {
        $attempts = collect([$this->attempt(1, 200, reset: true), $this->attempt(2, 50)]);
        $this->assertSame(2, $this->service()->pickOfficial($attempts, 'highest')->attempt_number);

        $this->assertNull($this->service()->pickOfficial(collect([$this->attempt(1, 200, reset: true)])));
        $this->assertNull($this->service()->pickOfficial(collect()));
    }

    public function test_survey_without_score_is_still_selectable(): void
    {
        $attempts = collect([$this->attempt(1, 0, 0), $this->attempt(2, 0, 0)]);

        $this->assertSame(2, $this->service()->pickOfficial($attempts, 'highest')->attempt_number);
    }

    public function test_max_attempts_rules(): void
    {
        $lesson = new CourseLesson;
        $s = $this->service();

        $lesson->forceFill(['survey_mode' => 'pre_test', 'max_attempts' => 9]);
        $this->assertSame(1, $s->maxAttempts($lesson), 'Pre test selalu 1');

        $lesson->forceFill(['survey_mode' => 'post_test', 'max_attempts' => 3]);
        $this->assertSame(3, $s->maxAttempts($lesson));

        $lesson->forceFill(['survey_mode' => 'post_test', 'max_attempts' => 0]);
        $this->assertSame(1, $s->maxAttempts($lesson), 'Minimal 1');

        $lesson->forceFill(['survey_mode' => 'post_test', 'max_attempts' => 999]);
        $this->assertSame(SurveyAttemptService::MAX_ATTEMPTS_LIMIT, $s->maxAttempts($lesson), 'Dibatasi maksimum');

        $lesson->forceFill(['survey_mode' => 'aneh', 'attempt_score_policy' => 'acak', 'max_attempts' => 2]);
        $this->assertSame('kuesioner', $s->mode($lesson));
        $this->assertSame('highest', $s->policy($lesson));
    }
}
