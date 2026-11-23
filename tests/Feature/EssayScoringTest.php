<?php

namespace Tests\Feature;

use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Services\SurveyAttemptService;
use App\Services\SurveyScoring;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Nilai esai dipisah dari nilai PG (keputusan user, Nov 2026).
 */
class EssayScoringTest extends TestCase
{
    use BuildsLessonFixtures;
    use DatabaseTransactions;

    private SurveyQuestion $qText2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildFixtures();
        $this->qText2 = SurveyQuestion::query()->create(['survey_id' => $this->survey->id, 'type' => 'text', 'question_text' => 'Esai 2', 'is_required' => false, 'urutan' => 5]);
    }

    private function submitWithTwoEssays($lesson, bool $pgCorrect = true): SurveyResponse
    {
        $this->submitSurvey($lesson, $this->answers($pgCorrect) + [$this->qText2->id => 'Jawaban esai kedua'])->assertSessionHas('success');

        return SurveyResponse::query()->where('user_id', $this->peserta->id)->where('course_lesson_id', $lesson->id)->latest('attempt_number')->firstOrFail();
    }

    private function essayAnswer(SurveyResponse $r, SurveyQuestion $q): SurveyAnswer
    {
        return $r->answers()->where('survey_question_id', $q->id)->firstOrFail();
    }

    private function grade(SurveyResponse $r, array $scores, $as = null)
    {
        return $this->actingAs($as ?? $this->admin)
            ->from(route('surveys.recap.index', $this->survey))
            ->post(route('surveys.recap.grade-essay', $r), ['scores' => $scores]);
    }

    public function test_submit_marks_essay_pending_and_pg_is_scored_alone(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2), false);

        $this->assertSame('pending_essay', $r->grading_status);
        $this->assertSame(2, $r->essay_answered_count);
        $this->assertSame(0, $r->essay_graded_count);
        $this->assertNull($r->essay_score);
        $this->assertSame('pending', SurveyScoring::essayStatus($r));
        $this->assertSame(0.0, SurveyScoring::percentage($r)); // PG salah semua
    }

    public function test_partial_then_full_grading_averages_essay_and_never_touches_pg(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2), true);
        $pgBefore = [$r->total_score, $r->max_possible_score];

        $this->grade($r, [$this->essayAnswer($r, $this->qText)->id => 80])->assertSessionHas('success');
        $r->refresh();
        $this->assertSame('pending_essay', $r->grading_status);
        $this->assertNull($r->essay_score);
        $this->assertSame(1, $r->essay_graded_count);

        $this->grade($r, [$this->essayAnswer($r, $this->qText2)->id => 61])->assertSessionHas('success');
        $r->refresh();
        $this->assertSame('completed', $r->grading_status);
        $this->assertSame('70.50', $r->essay_score);
        $this->assertSame('graded', SurveyScoring::essayStatus($r));
        $this->assertSame((int) $this->admin->id, (int) $r->essay_graded_by);
        $this->assertNotNull($r->essay_graded_at);
        $this->assertSame($pgBefore, [$r->total_score, $r->max_possible_score]);
        $this->assertSame(100.0, SurveyScoring::percentage($r));

        // Nilai ulang (revisi) tetap konsisten.
        $this->grade($r, [$this->essayAnswer($r, $this->qText)->id => 0, $this->essayAnswer($r, $this->qText2)->id => 100]);
        $this->assertSame('50.00', $r->fresh()->essay_score);
    }

    public function test_blank_input_keeps_essay_ungraded_and_recap_averages_are_separate(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $r = $this->submitWithTwoEssays($lesson, true);

        $this->grade($r, [$this->essayAnswer($r, $this->qText)->id => 90, $this->essayAnswer($r, $this->qText2)->id => ''])->assertSessionHas('success');
        $this->assertFalse((bool) $this->essayAnswer($r, $this->qText2)->is_graded);
        $this->assertNull($r->fresh()->essay_score);

        $this->grade($r, [$this->essayAnswer($r, $this->qText2)->id => 70]);
        $page = $this->actingAs($this->admin)->get(route('surveys.recap.index', $this->survey))->assertOk();
        $page->assertViewHas('overallStats', fn ($s) => $s['average_pg'] === 100.0 && $s['average_essay'] === 80.0 && $s['pending_essays'] === 0);

        $csv = $this->actingAs($this->admin)->get(route('surveys.recap.export-courses', $this->survey))->streamedContent();
        $this->assertStringContainsString('Rata-Rata Nilai Esai', $csv);
        $this->assertStringContainsString(';100,0;80,0', $csv);
    }

    public function test_empty_essay_is_not_counted(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $this->submitSurvey($lesson, array_replace($this->answers(), [$this->qText->id => '']))->assertSessionHas('success');
        $r = SurveyResponse::query()->where('course_lesson_id', $lesson->id)->firstOrFail();

        $this->assertSame(0, $r->essay_answered_count);
        $this->assertSame('completed', $r->grading_status);
        $this->assertSame('none', SurveyScoring::essayStatus($r));
    }

    public function test_abnormal_grading_input_is_rejected(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2));
        $id = $this->essayAnswer($r, $this->qText)->id;

        foreach ([101, -1, 'abc', 50.5, ['x'], '1e2', '0x10', '5abc'] as $bad) {
            $this->grade($r, [$id => $bad])->assertSessionHasErrors();
        }
        $this->grade($r, [])->assertSessionHasErrors('scores');
        // Semua kolom dikosongkan -> tidak ada yang disimpan sebagai 0.
        $this->grade($r, [$id => null, $this->essayAnswer($r, $this->qText2)->id => ''])->assertSessionHas('error');
        $this->actingAs($this->admin)->post(route('surveys.recap.grade-essay', $r), ['scores' => 'x'])->assertSessionHasErrors('scores');
        $this->grade($r, array_fill_keys(range(1, 201), 50))->assertSessionHasErrors('scores');

        $this->assertFalse((bool) $this->essayAnswer($r, $this->qText)->is_graded);
        $this->assertNull($r->fresh()->essay_score);
    }

    public function test_idor_answers_of_other_response_and_non_essay_answers_are_ignored(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $mine = $this->submitWithTwoEssays($lesson);
        $this->submitSurvey($lesson, $this->answers() + [$this->qText2->id => 'x'], $this->enrollOther())->assertSessionHas('success');
        $other = SurveyResponse::query()->where('user_id', $this->otherPeserta->id)->firstOrFail();

        $otherEssay = $this->essayAnswer($other, $this->qText);
        $myRadio = $this->essayAnswer($mine, $this->qRadio);
        $radioScoreBefore = $myRadio->score;

        // Kirim ID esai milik peserta lain + jawaban PG via response saya -> tidak ada yang tersimpan.
        $this->grade($mine, [$otherEssay->id => 99, $myRadio->id => 5, 'bukan-id' => 10])
            ->assertSessionHas('error');

        $this->assertFalse((bool) $otherEssay->fresh()->is_graded);
        $this->assertEquals($radioScoreBefore, $myRadio->fresh()->score);
        $this->assertNull($other->fresh()->essay_score);
        $this->assertSame(100.0, SurveyScoring::percentage($mine->fresh()));
    }

    public function test_only_admin_can_grade(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2));
        $id = $this->essayAnswer($r, $this->qText)->id;

        $this->actingAs($this->peserta)->postJson(route('surveys.recap.grade-essay', $r), ['scores' => [$id => 100]])->assertForbidden();
        $this->grade($r, [$id => 100], $this->peserta)->assertRedirect();
        $this->actingAs($this->peserta)->get(route('surveys.recap.index', $this->survey))->assertRedirect();
        $this->post(route('logout'));
        $this->app['auth']->forgetGuards();
        $this->post(route('surveys.recap.grade-essay', $r), ['scores' => [$id => 100]])->assertRedirect(route('login'));

        $this->assertFalse((bool) $this->essayAnswer($r, $this->qText)->fresh()->is_graded);
    }

    public function test_csrf_is_enforced_on_grade_endpoint(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2));
        $id = $this->essayAnswer($r, $this->qText)->id;

        // Middleware CSRF aktif kembali (default test mengabaikannya saat running unit test).
        $this->app->instance('env', 'local');
        $resp = $this->actingAs($this->admin)
            ->withMiddleware()
            ->call('POST', route('surveys.recap.grade-essay', $r), ['scores' => [$id => 100]]);
        $this->assertContains($resp->getStatusCode(), [419, 302]);
        $this->assertFalse((bool) $this->essayAnswer($r, $this->qText)->fresh()->is_graded);
    }

    public function test_xss_in_essay_is_escaped_on_recap_and_lesson_page(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $payload = '<script>alert("xss")</script><img src=x onerror=alert(1)>';
        $this->submitSurvey($lesson, array_replace($this->answers(), [$this->qText->id => $payload]))->assertSessionHas('success');

        $recap = $this->actingAs($this->admin)->get(route('surveys.recap.index', $this->survey))->assertOk();
        $recap->assertDontSee($payload, false)->assertSee('&lt;script&gt;alert', false);

        $page = $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))->assertOk();
        $page->assertDontSee($payload, false);
    }

    public function test_recap_page_lesson_page_and_export_show_pg_and_essay_separately(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $r = $this->submitWithTwoEssays($lesson, true);

        $this->actingAs($this->admin)->get(route('surveys.recap.index', $this->survey))
            ->assertOk()->assertSee('Nilai PG')->assertSee('Nilai Esai')->assertSee('Belum dinilai (0/2)');
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()->assertSee('Nilai PG')->assertSee('Menunggu')->assertSee('Benar 2 dari 2 soal');

        $this->grade($r, [$this->essayAnswer($r, $this->qText)->id => 90, $this->essayAnswer($r, $this->qText2)->id => 70]);

        $this->actingAs($this->admin)->get(route('surveys.recap.index', $this->survey))->assertOk()->assertSee('80.0');
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()->assertSee('80.0')->assertSee('2 esai dinilai pengajar');

        $csv = $this->actingAs($this->admin)->get(route('surveys.recap.export-participants', $this->survey))->streamedContent();
        $this->assertStringContainsString('Nilai PG (0-100)', $csv);
        $this->assertStringContainsString('Nilai Esai (0-100)', $csv);
        $this->assertStringContainsString(';100,0;80,0;2/2;Selesai;', $csv);
    }

    public function test_certificate_gate_uses_pg_only_and_ignores_essay(): void
    {
        $post = $this->surveyLesson('post_test', 2);
        $cert = $this->lesson('sertifikat', ['prerequisite_survey_id' => $this->survey->id, 'passing_grade' => 70, 'require_all_lessons' => false]);
        $r = $this->submitWithTwoEssays($post, true);

        // Esai belum dinilai / bernilai 0 tidak menurunkan nilai kelulusan (PG 100).
        $this->grade($r, [$this->essayAnswer($r, $this->qText)->id => 0, $this->essayAnswer($r, $this->qText2)->id => 0]);
        $gate = $this->callGate($cert);
        $this->assertTrue($gate['unlocked']);
        $this->assertSame(100.0, $gate['user_score']);
    }

    public function test_highest_pg_attempt_is_official_regardless_of_essay(): void
    {
        $lesson = $this->surveyLesson('post_test', 3);
        $first = $this->submitWithTwoEssays($lesson, true);   // PG 100
        $second = $this->submitWithTwoEssays($lesson, false); // PG 0
        $this->grade($second, [$this->essayAnswer($second, $this->qText)->id => 100, $this->essayAnswer($second, $this->qText2)->id => 100]);

        $official = app(SurveyAttemptService::class)->officialResponse($this->peserta, $lesson);
        $this->assertSame($first->id, $official->id);
    }

    public function test_recalculate_backfills_legacy_rows(): void
    {
        $r = $this->submitWithTwoEssays($this->surveyLesson('post_test', 2));
        $this->essayAnswer($r, $this->qText)->update(['score' => 40, 'is_graded' => true]);
        $this->essayAnswer($r, $this->qText2)->update(['score' => 60, 'is_graded' => true]);
        SurveyResponse::query()->whereKey($r->id)->update(['essay_score' => null, 'essay_answered_count' => 0, 'essay_graded_count' => 0, 'grading_status' => 'pending_essay', 'total_score' => 9999]);

        app(SurveyScoring::class)->recalculate($r->fresh());
        $r->refresh();

        $this->assertSame('50.00', $r->essay_score);
        $this->assertSame('completed', $r->grading_status);
        $this->assertSame('200.00', $r->total_score); // nilai esai lama yang tercampur dibuang
    }

    public function test_word_and_ppt_still_use_manual_mark_complete(): void
    {
        $doc = $this->lesson('berkas', ['file_url' => 'lessons/materi.docx']);
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $doc]))
            ->assertOk()->assertSee('Tandai selesai');
    }

    private function enrollOther()
    {
        \App\Models\CourseEnrollment::query()->create([
            'user_id' => $this->otherPeserta->id, 'course_id' => $this->course->id, 'progress' => 0,
            'modul_selesai' => 0, 'status' => 'Berlangsung', 'enrolled_at' => now(),
        ]);

        return $this->otherPeserta;
    }

    private function callGate($certLesson): array
    {
        $controller = app(\App\Http\Controllers\Peserta\LessonController::class);
        $progress = app(\App\Services\LearningProgressService::class);
        $this->actingAs($this->peserta);
        $method = new \ReflectionMethod($controller, 'evaluateCertificateGate');

        return $method->invoke($controller, $this->peserta, $this->course, $certLesson->fresh(), $progress->orderedLessons($this->course->fresh()),
            $progress->completedLessonIds($this->peserta, $this->course), \App\Models\CourseEnrollment::query()->where('user_id', $this->peserta->id)->first());
    }
}
