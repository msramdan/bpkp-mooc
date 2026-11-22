<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Services\Exceptions\SurveyAttemptException;
use App\Services\SurveyAttemptService;
use App\Services\SurveyScoring;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class SurveyAttemptsTest extends TestCase
{
    use BuildsLessonFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildFixtures();
    }

    private function responses($lesson)
    {
        return SurveyResponse::query()->where('user_id', $this->peserta->id)->where('course_lesson_id', $lesson->id)->orderBy('attempt_number')->get();
    }

    /* ---------------- Jumlah percobaan ---------------- */

    public function test_default_is_single_attempt_and_second_submit_is_rejected(): void
    {
        $lesson = $this->surveyLesson();

        $this->submitSurvey($lesson, $this->answers())->assertRedirect()->assertSessionHas('success');
        $this->submitSurvey($lesson, $this->answers(false))->assertRedirect()->assertSessionHas('error', 'Anda sudah mengirim jawaban untuk aktivitas ini.');

        $this->assertCount(1, $this->responses($lesson));
        $this->assertSame(100.0, SurveyScoring::percentage($this->responses($lesson)->first()));
    }

    public function test_post_test_allows_configured_attempts_then_blocks(): void
    {
        $lesson = $this->surveyLesson('post_test', 3);

        $this->submitSurvey($lesson, $this->answers(false))->assertSessionHas('success');
        $this->submitSurvey($lesson, $this->answers(true))->assertSessionHas('success');
        $this->submitSurvey($lesson, $this->answers(false))->assertSessionHas('success');
        $this->submitSurvey($lesson, $this->answers(true))->assertSessionHas('error', 'Batas percobaan (3 kali) sudah habis.');

        $this->assertSame([1, 2, 3], $this->responses($lesson)->pluck('attempt_number')->all());
    }

    public function test_pre_test_is_always_single_attempt_even_if_db_says_more(): void
    {
        $lesson = $this->surveyLesson('pre_test', 5);

        $this->assertSame(1, app(SurveyAttemptService::class)->maxAttempts($lesson));
        $this->submitSurvey($lesson, $this->answers())->assertSessionHas('success');
        $this->submitSurvey($lesson, $this->answers())->assertSessionHas('error');
        $this->assertCount(1, $this->responses($lesson));
    }

    public function test_official_score_policy_highest_and_latest(): void
    {
        $service = app(SurveyAttemptService::class);

        $highest = $this->surveyLesson('post_test', 3, 'highest');
        $this->submitSurvey($highest, $this->answers(true));   // 100
        $this->submitSurvey($highest, $this->answers(false));  // 0
        $official = $service->officialResponse($this->peserta, $highest);
        $this->assertSame(1, $official->attempt_number);
        $this->assertSame(100.0, SurveyScoring::percentage($official));

        $latest = $this->surveyLesson('post_test', 3, 'latest');
        $this->submitSurvey($latest, $this->answers(true));   // 100
        $this->submitSurvey($latest, $this->answers(false));  // 0
        $this->assertSame(2, $service->officialResponse($this->peserta, $latest)->attempt_number);
    }

    public function test_lesson_page_shows_attempt_info_retake_button_and_no_prefill(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $this->submitSurvey($lesson, $this->answers(false));

        $page = $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]));
        $page->assertOk()
            ->assertSee('Post Test')
            ->assertSee('Percobaan 1 dari 2')
            ->assertSee('Kerjakan lagi (sisa 1 percobaan)');

        // Percobaan ulang tidak boleh terisi jawaban sebelumnya.
        $pattern = '/value="('.preg_quote($this->opt['radio_bad']->id, '/').'|'.preg_quote($this->opt['cb_c']->id, '/').')"\s+checked/';
        $this->assertDoesNotMatchRegularExpression($pattern, $page->getContent());

        $this->submitSurvey($lesson, $this->answers(true));
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()->assertSee('Percobaan 2 dari 2')->assertDontSee('id="surveyRetakeBtn"', false)->assertDontSee('id="surveySubmitForm"', false);
    }

    /* ---------------- Race condition ---------------- */

    public function test_unique_index_backstop_turns_race_into_friendly_conflict(): void
    {
        $lesson = $this->surveyLesson('post_test', 3);
        $service = app(SurveyAttemptService::class);

        // Simulasikan request paralel yang sudah menyisipkan nomor percobaan yang sama.
        try {
            $service->submit($this->peserta, $this->course, $lesson, function (SurveyResponse $mine): void {
                SurveyResponse::query()->create([
                    'survey_id' => $mine->survey_id, 'user_id' => $mine->user_id,
                    'course_lesson_id' => $mine->course_lesson_id, 'attempt_number' => $mine->attempt_number,
                ]);
            });
            $this->fail('Harus melempar conflict');
        } catch (SurveyAttemptException $e) {
            $this->assertSame('conflict', $e->reason);
        }

        $this->assertCount(0, $this->responses($lesson), 'Transaksi gagal harus di-rollback seluruhnya');
    }

    public function test_service_rejects_when_limit_reached_inside_lock(): void
    {
        $lesson = $this->surveyLesson();
        $service = app(SurveyAttemptService::class);
        $service->submit($this->peserta, $this->course, $lesson, fn () => null);

        $this->expectException(SurveyAttemptException::class);
        $service->submit($this->peserta, $this->course, $lesson, fn () => null);
    }

    /* ---------------- Reset oleh admin ---------------- */

    public function test_admin_reset_keeps_history_and_allows_retake(): void
    {
        $lesson = $this->surveyLesson('pre_test');
        $this->submitSurvey($lesson, $this->answers(false));
        $first = $this->responses($lesson)->first();

        $this->actingAs($this->admin)->post(route('surveys.recap.reset-attempts', $first), ['reason' => 'Gangguan jaringan'])
            ->assertRedirect()->assertSessionHas('success');

        $first->refresh();
        $this->assertNotNull($first->reset_at);
        $this->assertSame($this->admin->id, $first->reset_by);
        $this->assertSame('Gangguan jaringan', $first->reset_reason);

        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()->assertSee('di-reset oleh admin')->assertSee('id="surveySubmitForm"', false);

        $this->submitSurvey($lesson, $this->answers(true))->assertSessionHas('success');
        $all = $this->responses($lesson);
        $this->assertCount(2, $all, 'Jawaban lama tetap tersimpan sebagai riwayat');
        $this->assertSame(2, $all->last()->attempt_number, 'Nomor percobaan berlanjut');
        $this->assertSame(100.0, SurveyScoring::percentage(app(SurveyAttemptService::class)->officialResponse($this->peserta, $lesson)));

        // Reset kedua kali pada jawaban yang sudah di-reset -> tidak ada yang aktif lagi dari jawaban lama itu? (ada yang baru aktif)
        $this->actingAs($this->admin)->post(route('surveys.recap.reset-attempts', $first))->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('surveys.recap.reset-attempts', $first))
            ->assertSessionHas('error', 'Tidak ada percobaan aktif yang bisa di-reset (mungkin sudah di-reset admin lain).');
    }

    public function test_peserta_cannot_reset_and_reason_is_validated(): void
    {
        $lesson = $this->surveyLesson();
        $this->submitSurvey($lesson, $this->answers());
        $resp = $this->responses($lesson)->first();

        $this->actingAs($this->peserta)->postJson(route('surveys.recap.reset-attempts', $resp))->assertForbidden();
        $this->actingAs($this->admin)->post(route('surveys.recap.reset-attempts', $resp), ['reason' => str_repeat('x', 300)])
            ->assertSessionHasErrors('reason');
        $this->assertNull($resp->fresh()->reset_at);
    }

    public function test_recap_shows_attempts_and_reset_and_export_includes_attempt(): void
    {
        $lesson = $this->surveyLesson('post_test', 2);
        $this->submitSurvey($lesson, $this->answers());

        $this->actingAs($this->admin)->get(route('surveys.recap.index', $this->survey))
            ->assertOk()->assertSee('Percobaan ke-1')->assertSee('Reset');

        $csv = $this->actingAs($this->admin)->get(route('surveys.recap.export-participants', $this->survey))->streamedContent();
        $this->assertStringContainsString('Percobaan Ke', $csv);
    }

    /* ---------------- Input abnormal ---------------- */

    public function test_abnormal_answers_are_rejected(): void
    {
        $lesson = $this->surveyLesson('post_test', 5);
        $foreignOption = $this->opt['cb_a']->id; // opsi milik soal checklist

        $cases = [
            'opsi soal lain di PG' => [$this->qRadio->id => $foreignOption, $this->qCheckbox->id => [$this->opt['cb_a']->id]],
            'opsi palsu di checklist' => [$this->qRadio->id => $this->opt['radio_ok']->id, $this->qCheckbox->id => [$this->opt['radio_ok']->id]],
            'checklist bukan array' => [$this->qRadio->id => $this->opt['radio_ok']->id, $this->qCheckbox->id => 'abc'],
            'PG berupa array' => [$this->qRadio->id => [$this->opt['radio_ok']->id], $this->qCheckbox->id => [$this->opt['cb_a']->id]],
            'esai terlalu panjang' => array_replace($this->answers(), [$this->qText->id => str_repeat('a', 10001)]),
            'wajib kosong' => [$this->qText->id => 'hanya esai'],
        ];

        foreach ($cases as $label => $answers) {
            $this->submitSurvey($lesson, $answers)->assertSessionHasErrors();
            $this->assertCount(0, $this->responses($lesson), "Kasus '{$label}' tidak boleh tersimpan");
        }

        // Rating 9 ditulis ulang dengan array_replace agar benar-benar diuji.
        $bad = array_replace($this->answers(), [$this->qRating->id => 9]);
        $this->submitSurvey($lesson, $bad)->assertSessionHasErrors('answers.'.$this->qRating->id);

        // answers bukan array sama sekali.
        $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.survey.submit', [$this->course, $lesson]), ['answers' => 'x'])
            ->assertSessionHasErrors();

        // Jawaban untuk soal yang tidak ada diabaikan, jawaban sah tetap tersimpan.
        $this->submitSurvey($lesson, $this->answers() + ['soal-palsu' => 'x'])->assertSessionHas('success');
        $this->assertCount(1, $this->responses($lesson));
    }

    public function test_access_rules_for_submit(): void
    {
        $lesson = $this->surveyLesson();

        // Peserta tidak terdaftar.
        $this->submitSurvey($lesson, $this->answers(), $this->otherPeserta)->assertForbidden();
        $this->assertSame(0, SurveyResponse::query()->where('user_id', $this->otherPeserta->id)->count());

        // Materi terkunci (materi wajib sebelumnya belum selesai).
        $gate = $this->lesson('url', ['is_required' => true, 'file_url' => 'https://example.com']);
        $locked = $this->surveyLesson(); // urutan setelah $gate yang wajib & belum selesai
        $this->submitSurvey($locked, $this->answers())->assertRedirect(route('peserta.kursus.show', $this->course));
        $this->assertCount(0, $this->responses($locked));

        // Lesson non-survey -> 404.
        $this->submitSurvey($gate, $this->answers())->assertNotFound();
    }

    /* ---------------- Admin: pengaturan & bug UUID ---------------- */

    public function test_admin_lesson_form_saves_attempt_settings_and_uuid_prerequisite(): void
    {
        $this->actingAs($this->admin)->post(route('courses.modules.lessons.store', [$this->course, $this->module]), [
            'judul' => 'Post Test Bab 1', 'tipe' => 'survey', 'survey_id' => $this->survey->id,
            'survey_mode' => 'post_test', 'max_attempts' => 3, 'attempt_score_policy' => 'latest', 'durasi_menit' => 10,
        ])->assertRedirect();

        $post = $this->module->lessons()->where('judul', 'Post Test Bab 1')->firstOrFail();
        $this->assertSame(['post_test', 3, 'latest'], [$post->survey_mode, $post->max_attempts, $post->attempt_score_policy]);

        $this->actingAs($this->admin)->post(route('courses.modules.lessons.store', [$this->course, $this->module]), [
            'judul' => 'Pre Test Bab 1', 'tipe' => 'survey', 'survey_id' => $this->survey->id,
            'survey_mode' => 'pre_test', 'max_attempts' => 7, 'durasi_menit' => 10,
        ])->assertRedirect();
        $this->assertSame(1, $this->module->lessons()->where('judul', 'Pre Test Bab 1')->value('max_attempts'));

        $this->actingAs($this->admin)->post(route('courses.modules.lessons.store', [$this->course, $this->module]), [
            'judul' => 'Salah', 'tipe' => 'survey', 'survey_id' => $this->survey->id, 'max_attempts' => 99, 'attempt_score_policy' => 'random',
        ])->assertSessionHasErrors(['max_attempts', 'attempt_score_policy']);

        // Regresi bug: UUID survey prasyarat dulu ter-cast menjadi angka.
        $this->actingAs($this->admin)->post(route('courses.modules.lessons.store', [$this->course, $this->module]), [
            'judul' => 'Sertifikat', 'tipe' => 'sertifikat', 'prerequisite_survey_id' => $this->survey->id, 'passing_grade' => 70,
        ])->assertRedirect();
        $this->assertSame((string) $this->survey->id, (string) $this->module->lessons()->where('judul', 'Sertifikat')->value('prerequisite_survey_id'));
    }

    public function test_certificate_gate_uses_official_active_score(): void
    {
        $post = $this->surveyLesson('post_test', 2, 'highest');
        $cert = $this->lesson('sertifikat', ['prerequisite_survey_id' => $this->survey->id, 'passing_grade' => 70, 'require_all_lessons' => false]);

        $this->submitSurvey($post, $this->answers(false)); // 0
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $cert]))->assertOk()->assertSee('0');

        $this->submitSurvey($post, $this->answers(true)); // 100 -> resmi (tertinggi)
        $gate = $this->callGate($cert);
        $this->assertTrue($gate['unlocked']);
        $this->assertSame(100.0, $gate['user_score']);

        // Reset: nilai lama tidak lagi dihitung -> prasyarat belum terpenuhi.
        app(SurveyAttemptService::class)->reset($this->peserta, $post, $this->admin);
        $gate = $this->callGate($cert);
        $this->assertFalse($gate['survey_completed']);
        $this->assertFalse($gate['unlocked']);
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
