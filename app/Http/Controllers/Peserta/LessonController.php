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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

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
        if ($lesson->normalizedType() === 'penugasan') {
            $submission = AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->where('course_lesson_id', $lesson->id)
                ->first();
        }

        return view('peserta.kursus.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'isCompleted' => $isCompleted,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'nextAccessible' => $nextLesson && $progress->isLessonAccessible(
                $user,
                $course,
                $nextLesson,
                $completedIds
            ),
            'submission' => $submission,
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

        $file = $request->file('submission_file');
        $path = $file->store('courses/submissions/'.$lesson->id, 'public');

        $existing = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $lesson->id)
            ->first();

        if ($existing) {
            if ($existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->update([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize() ?: 0,
                'mime_type' => $file->getClientMimeType(),
                'submitted_at' => now(),
            ]);
        } else {
            AssignmentSubmission::create([
                'user_id' => $user->id,
                'course_lesson_id' => $lesson->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize() ?: 0,
                'mime_type' => $file->getClientMimeType(),
                'submitted_at' => now(),
            ]);
        }

        $progress->completeLesson($user, $course, $lesson);

        return to_route('peserta.kursus.lessons.show', [$course, $lesson])
            ->with('success', __('Hasil pengerjaan berhasil diunggah.'));
    }
}
