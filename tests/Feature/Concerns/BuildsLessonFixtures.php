<?php

namespace Tests\Feature\Concerns;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Data uji bersama: kursus + modul + peserta terdaftar + survey (1 PG, 1 checklist, 1 esai, 1 rating).
 */
trait BuildsLessonFixtures
{
    protected User $peserta;

    protected User $otherPeserta;

    protected User $admin;

    protected Course $course;

    protected CourseModule $module;

    protected Survey $survey;

    protected SurveyQuestion $qRadio;

    protected SurveyQuestion $qCheckbox;

    protected SurveyQuestion $qText;

    protected SurveyQuestion $qRating;

    /** @var array<string, SurveyOption> */
    protected array $opt = [];

    protected function buildFixtures(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        Role::findByName(Roles::PESERTA)->givePermissionTo(['peserta dashboard view', 'peserta kursus view', 'peserta katalog view']);

        $this->peserta = User::factory()->create(['email' => 'p-'.Str::random(8).'@test.local']);
        $this->peserta->assignRole(Roles::PESERTA);
        $this->otherPeserta = User::factory()->create(['email' => 'o-'.Str::random(8).'@test.local']);
        $this->otherPeserta->assignRole(Roles::PESERTA);
        $this->admin = User::factory()->create(['email' => 'a-'.Str::random(8).'@test.local']);
        $this->admin->assignRole(Roles::SUPER_ADMIN);

        $kode = 'FX-'.Str::upper(Str::random(5));
        $this->course = Course::query()->create([
            'kode' => $kode, 'judul' => 'Kursus Fixture '.$kode, 'slug' => Str::slug($kode), 'kategori' => 'Uji',
            'instruktur' => 'Tester', 'durasi_jam' => 1, 'modul_total' => 1, 'level' => 'Pemula',
            'rating' => 0, 'deskripsi' => 'Test', 'is_published' => true,
        ]);
        $this->module = CourseModule::query()->create(['course_id' => $this->course->id, 'urutan' => 1, 'judul' => 'Modul 1', 'durasi_menit' => 10]);

        CourseEnrollment::query()->create([
            'user_id' => $this->peserta->id, 'course_id' => $this->course->id, 'progress' => 0,
            'modul_selesai' => 0, 'status' => 'Berlangsung', 'enrolled_at' => now(),
        ]);

        $this->survey = Survey::query()->create(['title' => 'Survey Fixture', 'description' => null, 'is_active' => true]);
        $this->qRadio = SurveyQuestion::query()->create(['survey_id' => $this->survey->id, 'type' => 'radio', 'question_text' => 'PG', 'is_required' => true, 'urutan' => 1]);
        $this->qCheckbox = SurveyQuestion::query()->create(['survey_id' => $this->survey->id, 'type' => 'checkbox', 'question_text' => 'Checklist', 'is_required' => true, 'urutan' => 2]);
        $this->qText = SurveyQuestion::query()->create(['survey_id' => $this->survey->id, 'type' => 'text', 'question_text' => 'Esai', 'is_required' => false, 'urutan' => 3]);
        $this->qRating = SurveyQuestion::query()->create(['survey_id' => $this->survey->id, 'type' => 'rating', 'question_text' => 'Rating', 'is_required' => false, 'urutan' => 4]);

        $mk = fn ($q, $text, $correct, $urut) => SurveyOption::query()->create([
            'survey_question_id' => $q->id, 'option_text' => $text, 'urutan' => $urut,
            'score_value' => $correct ? 100 : 0, 'is_correct' => $correct,
        ]);
        $this->opt['radio_ok'] = $mk($this->qRadio, 'Benar', true, 1);
        $this->opt['radio_bad'] = $mk($this->qRadio, 'Salah', false, 2);
        $this->opt['cb_a'] = $mk($this->qCheckbox, 'A', true, 1);
        $this->opt['cb_b'] = $mk($this->qCheckbox, 'B', true, 2);
        $this->opt['cb_c'] = $mk($this->qCheckbox, 'C', false, 3);
    }

    protected function lesson(string $tipe, array $attrs = []): CourseLesson
    {
        return CourseLesson::query()->create(array_merge([
            'course_module_id' => $this->module->id,
            'urutan' => ((int) CourseLesson::query()->where('course_module_id', $this->module->id)->max('urutan')) + 1,
            'judul' => ucfirst($tipe).' '.Str::random(4),
            'tipe' => $tipe,
            'is_preview' => false,
            'is_required' => false,
        ], $attrs));
    }

    protected function surveyLesson(string $mode = 'kuesioner', int $max = 1, string $policy = 'highest'): CourseLesson
    {
        return $this->lesson('survey', [
            'survey_id' => $this->survey->id,
            'survey_mode' => $mode,
            'max_attempts' => $max,
            'attempt_score_policy' => $policy,
        ]);
    }

    /** Jawaban 100 (semua benar) atau 0 (semua salah). */
    protected function answers(bool $allCorrect = true): array
    {
        return [
            $this->qRadio->id => $allCorrect ? $this->opt['radio_ok']->id : $this->opt['radio_bad']->id,
            $this->qCheckbox->id => $allCorrect ? [$this->opt['cb_a']->id, $this->opt['cb_b']->id] : [$this->opt['cb_c']->id],
            $this->qText->id => 'Esai saya',
            $this->qRating->id => 4,
        ];
    }

    protected function submitSurvey(CourseLesson $lesson, array $answers, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->peserta)
            ->post(route('peserta.kursus.lessons.survey.submit', [$this->course, $lesson]), ['answers' => $answers]);
    }
}
