<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\StoreAssignmentSubmissionRequest;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\LearningProgressService;
use App\Support\ActivityTypes;
use App\Support\PesertaAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SurveyResponse;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Services\CertificateService;
use App\Services\Exceptions\SurveyAttemptException;
use App\Services\LessonViewTracker;
use App\Services\SurveyAttemptService;
use App\Services\SurveyScoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:peserta'),
        ];
    }

    public function show(Course $course, CourseLesson $lesson, LearningProgressService $progress, SurveyAttemptService $attempts, LessonViewTracker $tracker): View|RedirectResponse
    {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course)) {
            abort(404);
        }

        $user = PesertaAccess::user();
        $enrollment = PesertaAccess::enrollmentForCourse($course);
        $completedIds = $progress->completedLessonIds($user, $course);

        if (! $progress->isLessonAccessible($user, $course, $lesson, $completedIds)) {
            return to_route('peserta.kursus.show', $course)
                ->with('error', __('Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'));
        }

        $lesson->load('module');
        $course->load([
            'modules' => fn ($q) => $q->orderBy('urutan')->with([
                'lessons' => fn ($q) => $q->orderBy('urutan'),
            ]),
        ]);

        $ordered = $progress->orderedLessons($course);
        $currentIndex = $ordered->search(fn (CourseLesson $l) => $l->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $ordered->get($currentIndex - 1) : null;
        $nextLesson = $ordered->get($currentIndex + 1);
        $isCompleted = $completedIds->contains($lesson->id);

        $submission = null;
        $surveyResponse = null;
        $attemptInfo = null;
        $viewTracking = null;

        if ($lesson->normalizedType() === 'penugasan') {
            $submission = AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->where('course_lesson_id', $lesson->id)
                ->first();
        } elseif ($lesson->normalizedType() === 'survey' || $lesson->survey_id) {
            $lesson->load('survey.questions.options');
            $active = $attempts->activeAttempts($user, $lesson)->load('answers');
            $surveyResponse = $attempts->officialResponse($user, $lesson, $active);

            $attemptInfo = [
                'mode' => $attempts->mode($lesson),
                'mode_label' => SurveyAttemptService::MODES[$attempts->mode($lesson)],
                'policy' => $attempts->policy($lesson),
                'policy_label' => SurveyAttemptService::POLICIES[$attempts->policy($lesson)],
                'max' => $attempts->maxAttempts($lesson),
                'used' => $active->count(),
                'remaining' => $attempts->remaining($user, $lesson, $active),
                'attempts' => $active,
                'was_reset' => $active->isEmpty() && SurveyResponse::query()
                    ->where('user_id', $user->id)->where('course_lesson_id', $lesson->id)->whereNotNull('reset_at')->exists(),
            ];

            if (! $surveyResponse) {
                $isCompleted = false;
            }
        }

        if ($tracker->trackingKind($lesson) !== null) {
            $viewTracking = $tracker->state($user, $lesson);
        }

        $certGate = null;
        if ($lesson->normalizedType() === 'sertifikat') {
            $certGate = $this->evaluateCertificateGate($user, $course, $lesson, $ordered, $completedIds, $enrollment);
            if ($certGate['unlocked'] && ! $isCompleted) {
                $progress->completeLesson($user, $course, $lesson);
                $isCompleted = true;
            }
        }

        return view('peserta.kursus.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'isCompleted' => $isCompleted,
            'certGate' => $certGate,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'nextAccessible' => $nextLesson && $progress->isLessonAccessible(
                $user,
                $course,
                $nextLesson,
                $completedIds
            ),
            'submission' => $submission,
            'surveyResponse' => $surveyResponse,
            'attemptInfo' => $attemptInfo,
            'viewTracking' => $viewTracking,
            'typeMeta' => ActivityTypes::find($lesson->normalizedType()),
        ]);
    }

    /**
     * Heartbeat progres video/dokumen dari player. Semua angka divalidasi ulang di server.
     */
    public function trackProgress(Request $request, Course $course, CourseLesson $lesson, LearningProgressService $progress, LessonViewTracker $tracker): JsonResponse
    {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course) || $tracker->trackingKind($lesson) === null) {
            abort(404);
        }

        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        if (! $progress->isLessonAccessible($user, $course, $lesson)) {
            return response()->json(['ok' => false, 'message' => __('Materi ini masih terkunci.')], 403);
        }

        $data = $request->validate([
            'position' => ['nullable', 'numeric'],
            'duration' => ['nullable', 'numeric'],
            'ended' => ['nullable', 'boolean'],
            'total_pages' => ['nullable', 'integer'],
            'pages' => ['nullable', 'array', 'max:200'],
            'pages.*' => ['integer'],
        ]);

        return response()->json(['ok' => true] + $tracker->heartbeat($user, $course, $lesson, $data));
    }

    public function complete(Course $course, CourseLesson $lesson, LearningProgressService $progress, LessonViewTracker $tracker): RedirectResponse
    {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course)) {
            abort(404);
        }

        if ($lesson->normalizedType() === 'penugasan') {
            $hasSubmission = AssignmentSubmission::query()
                ->where('user_id', PesertaAccess::user()->id)
                ->where('course_lesson_id', $lesson->id)
                ->exists();

            if (! $hasSubmission) {
                return back()->with('error', __('Unggah hasil pengerjaan terlebih dahulu sebelum menandai selesai.'));
            }
        }

        if (in_array($lesson->normalizedType(), ['survey', 'pre_test', 'post_test'], true) || $lesson->survey_id) {
            $hasSurveyResponse = \App\Models\SurveyResponse::query()
                ->where('user_id', PesertaAccess::user()->id)
                ->where('course_lesson_id', $lesson->id)
                ->active()
                ->exists();

            if (! $hasSurveyResponse) {
                return back()->with('error', __('Silakan isi dan kirim kuesioner/soal terlebih dahulu sebelum menyelesaikan materi ini.'));
            }
        }

        // Video & dokumen yang dilacak: selesai otomatis, tidak bisa ditandai manual.
        if ($tracker->trackingKind($lesson) !== null && ! $tracker->isCompleted(PesertaAccess::user(), $lesson)) {
            return back()->with('error', $tracker->trackingKind($lesson) === 'video'
                ? __('Tonton video sampai selesai untuk menyelesaikan materi ini.')
                : __('Baca dokumen sampai halaman terakhir untuk menyelesaikan materi ini.'));
        }

        $enrollment = $progress->completeLesson(PesertaAccess::user(), $course, $lesson);

        $ordered = $progress->orderedLessons($course);
        $currentIndex = $ordered->search(fn (CourseLesson $l) => $l->id === $lesson->id);
        $nextLesson = $ordered->get($currentIndex + 1);
        $completedIds = $progress->completedLessonIds(PesertaAccess::user(), $course);

        if ($nextLesson && $progress->isLessonAccessible(PesertaAccess::user(), $course, $nextLesson, $completedIds)) {
            return to_route('peserta.kursus.lessons.show', [$course, $nextLesson])
                ->with('success', __('Materi ditandai selesai.'));
        }

        return to_route('peserta.kursus.show', $course)
            ->with('success', $enrollment->progress >= 100
                ? __('Selamat! Anda telah menyelesaikan kursus ini.')
                : __('Materi ditandai selesai.'));
    }

    public function submit(
        StoreAssignmentSubmissionRequest $request,
        Course $course,
        CourseLesson $lesson,
        LearningProgressService $progress
    ): RedirectResponse {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course) || $lesson->normalizedType() !== 'penugasan') {
            abort(404);
        }

        $user = PesertaAccess::user();
        $completedIds = $progress->completedLessonIds($user, $course);

        if (! $progress->isLessonAccessible($user, $course, $lesson, $completedIds)) {
            return to_route('peserta.kursus.show', $course)
                ->with('error', __('Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'));
        }

        $existing = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $lesson->id)
            ->first();

        $file = $request->file('submission_file');
        $path = $existing?->file_path;
        $originalName = $existing?->original_name;
        $fileSize = $existing?->file_size ?? 0;
        $mimeType = $existing?->mime_type;

        if ($file) {
            $path = $file->store('courses/submissions/'.$lesson->id, 'public');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize() ?: 0;
            $mimeType = $file->getClientMimeType();
        }

        if ($existing) {
            if ($file && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->update([
                'submission_text' => $request->input('submission_text'),
                'submission_link' => $request->input('submission_link'),
                'file_path' => $path,
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'submitted_at' => now(),
            ]);
        } else {
            AssignmentSubmission::create([
                'user_id' => $user->id,
                'course_lesson_id' => $lesson->id,
                'submission_text' => $request->input('submission_text'),
                'submission_link' => $request->input('submission_link'),
                'file_path' => $path,
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'submitted_at' => now(),
            ]);
        }

        $progress->completeLesson($user, $course, $lesson);

        return to_route('peserta.kursus.lessons.show', [$course, $lesson])
            ->with('success', __('Jawaban penugasan berhasil disimpan.'));
    }

    public function submitSurvey(
        Request $request,
        Course $course,
        CourseLesson $lesson,
        LearningProgressService $progress,
        SurveyAttemptService $attempts,
        SurveyScoring $scoring
    ): RedirectResponse {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course) || $lesson->normalizedType() !== 'survey' || ! $lesson->survey_id) {
            abort(404);
        }

        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);
        $completedIds = $progress->completedLessonIds($user, $course);

        if (! $progress->isLessonAccessible($user, $course, $lesson, $completedIds)) {
            return to_route('peserta.kursus.show', $course)
                ->with('error', __('Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'));
        }

        // Cek cepat sebelum validasi (pengecekan final tetap di dalam lock).
        if ($attempts->remaining($user, $lesson) <= 0) {
            return to_route('peserta.kursus.lessons.show', [$course, $lesson])
                ->with('error', $attempts->maxAttempts($lesson) === 1
                    ? __('Anda sudah mengirim jawaban untuk aktivitas ini.')
                    : __('Batas percobaan (:n kali) sudah habis.', ['n' => $attempts->maxAttempts($lesson)]));
        }

        $lesson->load('survey.questions.options');
        $answers = $this->validatedAnswers($request, $lesson);

        try {
            $attempts->submit($user, $course, $lesson, function ($response) use ($lesson, $answers, $scoring): void {
                foreach ($lesson->survey->questions as $q) {
                    $answerVal = $answers[$q->id] ?? null;
                    if ($answerVal === null || $answerVal === '' || $answerVal === []) {
                        continue;
                    }

                    if ($q->type === 'rating') {
                        $response->answers()->create([
                            'survey_question_id' => $q->id,
                            'answer_text' => (string) $answerVal,
                            'score' => (int) $answerVal,
                            'is_graded' => true,
                        ]);
                    } elseif ($q->type === 'radio') {
                        $option = $q->options->firstWhere('id', $answerVal);
                        $response->answers()->create([
                            'survey_question_id' => $q->id,
                            'survey_option_id' => $option->id,
                            'score' => ($option->is_correct || $option->score_value >= 100) ? 100 : 0,
                            'is_graded' => true,
                        ]);
                    } elseif ($q->type === 'checkbox') {
                        foreach (array_values(array_unique($answerVal)) as $optId) {
                            $response->answers()->create([
                                'survey_question_id' => $q->id,
                                'survey_option_id' => $optId,
                                'score' => 0,
                                'is_graded' => true,
                            ]);
                        }
                    } elseif ($q->type === 'text') {
                        $response->answers()->create([
                            'survey_question_id' => $q->id,
                            'answer_text' => (string) $answerVal,
                            'score' => 0,
                            'is_graded' => false,
                        ]);
                    }
                }

                // Skor resmi: hanya PG & checklist berkunci yang dihitung (lihat SurveyScoring).
                $scoring->recalculate($response->fresh());
            });
        } catch (SurveyAttemptException $e) {
            return to_route('peserta.kursus.lessons.show', [$course, $lesson])->with('error', $e->getMessage());
        }

        $progress->completeLesson($user, $course, $lesson);

        return to_route('peserta.kursus.lessons.show', [$course, $lesson])
            ->with('success', __('Terima kasih! Jawaban berhasil dikirim.'));
    }

    /**
     * Validasi jawaban: wajib diisi, opsi harus milik soal tersebut, nilai rating dalam rentang,
     * teks dibatasi panjangnya. Jawaban untuk soal yang tidak ada di survey diabaikan.
     *
     * @return array<string, mixed>
     */
    private function validatedAnswers(Request $request, CourseLesson $lesson): array
    {
        $raw = $request->input('answers', []);
        $raw = is_array($raw) ? $raw : [];
        $errors = [];
        $clean = [];

        foreach ($lesson->survey->questions as $q) {
            $key = 'answers.'.$q->id;
            $val = $raw[$q->id] ?? null;
            $optionIds = $q->options->pluck('id')->map(fn ($id) => (string) $id)->all();
            $empty = $val === null || $val === '' || $val === [];

            if ($empty) {
                if ($q->is_required) {
                    $errors[$key] = __('Pertanyaan ":text" wajib diisi.', ['text' => $q->question_text]);
                }

                continue;
            }

            $invalid = __('Jawaban untuk pertanyaan ":text" tidak valid.', ['text' => $q->question_text]);

            switch ($q->type) {
                case 'radio':
                    if (! is_scalar($val) || ! in_array((string) $val, $optionIds, true)) {
                        $errors[$key] = $invalid;
                    } else {
                        $clean[$q->id] = (string) $val;
                    }
                    break;
                case 'checkbox':
                    $vals = is_array($val) ? array_map('strval', array_filter($val, 'is_scalar')) : [];
                    if ($vals === [] || array_diff($vals, $optionIds) !== [] || count($vals) !== count(is_array($val) ? $val : [])) {
                        $errors[$key] = $invalid;
                    } else {
                        $clean[$q->id] = $vals;
                    }
                    break;
                case 'rating':
                    if (! is_scalar($val) || filter_var($val, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) === false) {
                        $errors[$key] = $invalid;
                    } else {
                        $clean[$q->id] = (int) $val;
                    }
                    break;
                case 'text':
                    if (! is_scalar($val) || mb_strlen((string) $val) > 10000) {
                        $errors[$key] = __('Jawaban untuk pertanyaan ":text" terlalu panjang (maks 10.000 karakter).', ['text' => $q->question_text]);
                    } else {
                        $clean[$q->id] = trim((string) $val);
                    }
                    break;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $clean;
    }

    public function printCertificate(Course $course, CourseLesson $lesson, LearningProgressService $progress, CertificateService $certService): View|RedirectResponse
    {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course)) {
            abort(404);
        }

        $user = PesertaAccess::user();
        $enrollment = PesertaAccess::enrollmentForCourse($course);
        $completedIds = $progress->completedLessonIds($user, $course);
        $ordered = $progress->orderedLessons($course);
        $certGate = $this->evaluateCertificateGate($user, $course, $lesson, $ordered, $completedIds, $enrollment);

        if (! $certGate['unlocked']) {
            return back()->with('error', __('Sertifikat masih terkunci. Selesaikan seluruh prasyarat dan penuhi nilai minimal kelulusan terlebih dahulu.'));
        }

        $userScore = (int) round($certGate['user_score']);
        $templateId = $lesson->certificate_template_id ?? CertificateTemplate::where('is_default', true)->value('id');
        
        $certificate = Certificate::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'nomor' => 'SERT-' . str_pad((string) $course->id, 3, '0', STR_PAD_LEFT) . '/' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) . '/MOOC/' . date('Y'),
                'certificate_template_id' => $templateId,
                'nilai_akhir' => min(100, max(0, $userScore)),
                'issued_at' => now(),
            ]
        );

        $updateData = [];
        // Always ensure template_id points to a valid template (fixes stale integer IDs from pre-UUID migration)
        if (! $certificate->template && $templateId) {
            $updateData['certificate_template_id'] = $templateId;
        }
        if (isset($certificate->nilai_akhir) && (int) $certificate->nilai_akhir !== min(100, max(0, $userScore))) {
            $updateData['nilai_akhir'] = min(100, max(0, $userScore));
        }
        if (!empty($updateData)) {
            $certificate->update($updateData);
            $certificate->load('template'); // reload after fix
        }

        if (! $completedIds->contains($lesson->id)) {
            $progress->completeLesson($user, $course, $lesson);
        }

        $certificate->load(['course', 'user', 'template']);

        return view('peserta.kursus.certificate_print', [
            'certificate' => $certificate,
            'template' => $certificate->template ?? CertificateTemplate::where('is_default', true)->first(),
            'course' => $course,
            'user' => $user,
            'lesson' => $lesson,
        ]);
    }

    private function evaluateCertificateGate($user, Course $course, CourseLesson $lesson, $ordered, $completedIds, $enrollment): array
    {
        $allRequiredCompleted = true;
        if ($lesson->require_all_lessons ?? true) {
            $requiredLessons = $ordered->filter(fn (CourseLesson $l) => $l->id !== $lesson->id && $l->is_required && $l->tipe !== 'sertifikat');
            foreach ($requiredLessons as $reqLesson) {
                if (! $completedIds->contains($reqLesson->id)) {
                    $allRequiredCompleted = false;
                    break;
                }
            }
        }

        $surveyRequirementCompleted = true;
        $prereqSurvey = null;
        $userScore = 0;
        $scoreCalculated = false;

        $attempts = app(SurveyAttemptService::class);
        $courseLessonIds = $ordered->pluck('id')->map(fn ($id) => (string) $id)->all();

        if ($lesson->prerequisite_survey_id) {
            $prereqSurvey = \App\Models\Survey::find($lesson->prerequisite_survey_id);
            // Nilai resmi (percobaan aktif, sesuai kebijakan); utamakan materi di kursus ini.
            $official = $attempts->officialBySurveys($user, [(string) $lesson->prerequisite_survey_id]);
            $inCourse = $official->filter(fn ($r, $key) => in_array((string) $key, $courseLessonIds, true));
            $prereqResp = ($inCourse->isNotEmpty() ? $inCourse : $official)
                ->sortByDesc(fn ($r) => SurveyScoring::percentage($r))
                ->first();

            if ($prereqResp) {
                $surveyRequirementCompleted = true;
                if ($prereqResp->max_possible_score > 0) {
                    $userScore = SurveyScoring::percentage($prereqResp);
                    $scoreCalculated = true;
                }
            } else {
                $surveyRequirementCompleted = false;
            }
        }

        if (! $scoreCalculated) {
            $courseSurveyIds = $ordered->where('survey_id', '!=', null)->pluck('survey_id')->map(fn ($id) => (string) $id)->unique()->values()->all();
            if (! empty($courseSurveyIds)) {
                // Satu nilai resmi per materi di kursus ini (percobaan aktif saja).
                $responses = $attempts->officialBySurveys($user, $courseSurveyIds)
                    ->filter(fn ($r, $key) => in_array((string) $key, $courseLessonIds, true))
                    ->filter(fn ($r) => $r->max_possible_score > 0);

                if ($responses->count() > 0) {
                    $userScore = $responses->avg(fn ($r) => SurveyScoring::percentage($r));
                    $scoreCalculated = true;
                }
            }
        }

        if (! $scoreCalculated) {
            $userScore = $allRequiredCompleted ? 100 : ($enrollment ? $enrollment->progress : 0);
        }

        $passingGrade = (float) ($lesson->passing_grade ?? 0);
        $passedGrade = $passingGrade <= 0 || $userScore >= $passingGrade;

        $isGateUnlocked = $allRequiredCompleted && $surveyRequirementCompleted && $passedGrade;

        return [
            'unlocked' => $isGateUnlocked,
            'all_required_completed' => $allRequiredCompleted,
            'survey_completed' => $surveyRequirementCompleted,
            'prereq_survey' => $prereqSurvey,
            'passing_grade' => $passingGrade,
            'user_score' => round($userScore, 1),
            'passed_grade' => $passedGrade,
        ];
    }
}
