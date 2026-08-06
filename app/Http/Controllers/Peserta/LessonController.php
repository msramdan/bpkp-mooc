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

class LessonController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:peserta'),
        ];
    }

    public function show(Course $course, CourseLesson $lesson, LearningProgressService $progress): View|RedirectResponse
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

        if ($lesson->normalizedType() === 'penugasan') {
            $submission = AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->where('course_lesson_id', $lesson->id)
                ->first();
        } elseif ($lesson->normalizedType() === 'survey' || $lesson->survey_id) {
            $lesson->load('survey.questions.options');
            $surveyResponse = SurveyResponse::with('answers')->where('survey_id', $lesson->survey_id)
                ->where('user_id', $user->id)
                ->where('course_lesson_id', $lesson->id)
                ->first();

            if (! $surveyResponse) {
                $isCompleted = false;
            }
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
            'typeMeta' => ActivityTypes::find($lesson->normalizedType()),
        ]);
    }

    public function complete(Course $course, CourseLesson $lesson, LearningProgressService $progress): RedirectResponse
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
                ->exists();

            if (! $hasSurveyResponse) {
                return back()->with('error', __('Silakan isi dan kirim kuesioner/soal terlebih dahulu sebelum menyelesaikan materi ini.'));
            }
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
        LearningProgressService $progress
    ): RedirectResponse {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course) || $lesson->normalizedType() !== 'survey') {
            abort(404);
        }

        $user = PesertaAccess::user();
        $completedIds = $progress->completedLessonIds($user, $course);

        if (! $progress->isLessonAccessible($user, $course, $lesson, $completedIds)) {
            return to_route('peserta.kursus.show', $course)
                ->with('error', __('Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'));
        }

        $lesson->load('survey.questions');

        // Validation for required questions
        $rules = [];
        $messages = [];
        foreach ($lesson->survey->questions as $question) {
            if ($question->is_required) {
                if ($question->type === 'checkbox') {
                    $rules['answers.' . $question->id] = ['required', 'array'];
                    $messages['answers.' . $question->id . '.required'] = __('Pertanyaan ":text" wajib diisi.', ['text' => $question->question_text]);
                } else {
                    $rules['answers.' . $question->id] = ['required'];
                    $messages['answers.' . $question->id . '.required'] = __('Pertanyaan ":text" wajib diisi.', ['text' => $question->question_text]);
                }
            }
        }

        $validated = $request->validate($rules, $messages);
        $answers = $request->input('answers', []);

        DB::transaction(function () use ($lesson, $user, $answers) {
            $response = SurveyResponse::firstOrCreate([
                'survey_id' => $lesson->survey_id,
                'user_id' => $user->id,
                'course_lesson_id' => $lesson->id,
            ]);

            // Clear old answers if updating
            $response->answers()->delete();

            $totalScore = 0;
            $maxPossibleScore = 0;
            $hasPendingEssay = false;

            foreach ($lesson->survey->questions as $q) {
                $answerVal = $answers[$q->id] ?? null;
                if ($answerVal === null && !$q->is_required) {
                    continue;
                }

                if ($q->type === 'rating') {
                    $maxPossibleScore += 5;
                    $score = (int) $answerVal;
                    $totalScore += $score;
                    $response->answers()->create([
                        'survey_question_id' => $q->id,
                        'answer_text' => $answerVal,
                        'score' => $score,
                        'is_graded' => true,
                    ]);
                } elseif ($q->type === 'radio') {
                    $maxPossibleScore += 100;
                    $score = 0;
                    if ($answerVal) {
                        $option = $q->options()->where('id', $answerVal)->first();
                        if ($option && ($option->is_correct || $option->score_value >= 100)) {
                            $score = 100;
                        }
                    }
                    $totalScore += $score;
                    $response->answers()->create([
                        'survey_question_id' => $q->id,
                        'survey_option_id' => $answerVal ?: null,
                        'score' => $score,
                        'is_graded' => true,
                    ]);
                } elseif ($q->type === 'checkbox') {
                    $maxPossibleScore += 100;
                    $selectedIds = is_array($answerVal) ? $answerVal : [];
                    $correctIds = $q->options()->where('is_correct', true)->pluck('id')->toArray();
                    
                    $score = 0;
                    if (count($correctIds) > 0) {
                        sort($selectedIds);
                        sort($correctIds);
                        if (count($selectedIds) === count($correctIds) && empty(array_diff($selectedIds, $correctIds))) {
                            $score = 100;
                        } else {
                            $score = 0;
                        }
                    } else {
                        $score = count($selectedIds) > 0 ? 100 : 0;
                    }

                    $totalScore += $score;

                    if (is_array($answerVal)) {
                        foreach ($answerVal as $idx => $optId) {
                            $response->answers()->create([
                                'survey_question_id' => $q->id,
                                'survey_option_id' => $optId,
                                'score' => $idx === 0 ? $score : 0,
                                'is_graded' => true,
                            ]);
                        }
                    }
                } elseif ($q->type === 'text') {
                    $maxPossibleScore += 100;
                    $hasPendingEssay = true;
                    $response->answers()->create([
                        'survey_question_id' => $q->id,
                        'answer_text' => is_array($answerVal) ? null : $answerVal,
                        'score' => 0,
                        'is_graded' => false,
                    ]);
                }
            }

            $response->update([
                'total_score' => $totalScore,
                'max_possible_score' => $maxPossibleScore,
                'grading_status' => $hasPendingEssay ? 'pending_essay' : 'completed',
            ]);
        });

        $progress->completeLesson($user, $course, $lesson);

        return to_route('peserta.kursus.lessons.show', [$course, $lesson])
            ->with('success', __('Terima kasih! Kuesioner berhasil dikirim.'));
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
        if (! $certificate->certificate_template_id && $templateId) {
            $updateData['certificate_template_id'] = $templateId;
        }
        if (isset($certificate->nilai_akhir) && (int) $certificate->nilai_akhir !== min(100, max(0, $userScore))) {
            $updateData['nilai_akhir'] = min(100, max(0, $userScore));
        }
        if (!empty($updateData)) {
            $certificate->update($updateData);
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
            $requiredLessons = $ordered->filter(fn (CourseLesson $l) => $l->id !== $lesson->id && ($l->is_required || $l->tipe !== 'sertifikat'));
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

        if ($lesson->prerequisite_survey_id) {
            $prereqSurvey = \App\Models\Survey::find($lesson->prerequisite_survey_id);
            $prereqResp = SurveyResponse::where('survey_id', $lesson->prerequisite_survey_id)
                ->where('user_id', $user->id)
                ->first();
            if ($prereqResp) {
                $surveyRequirementCompleted = true;
                if ($prereqResp->max_possible_score > 0) {
                    $userScore = ($prereqResp->total_score / $prereqResp->max_possible_score) * 100;
                    $scoreCalculated = true;
                }
            } else {
                $surveyRequirementCompleted = false;
            }
        }

        if (! $scoreCalculated) {
            $courseSurveyIds = $ordered->where('survey_id', '!=', null)->pluck('survey_id')->unique()->toArray();
            if (!empty($courseSurveyIds)) {
                $responses = SurveyResponse::whereIn('survey_id', $courseSurveyIds)
                    ->where('user_id', $user->id)
                    ->where('max_possible_score', '>', 0)
                    ->get();
                if ($responses->count() > 0) {
                    $userScore = $responses->sum(fn($r) => ($r->total_score / $r->max_possible_score) * 100) / $responses->count();
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
