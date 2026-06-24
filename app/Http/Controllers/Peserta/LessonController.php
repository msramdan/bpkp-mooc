<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\LearningProgressService;
use App\Support\PesertaAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
        ]);
    }

    public function complete(Course $course, CourseLesson $lesson, LearningProgressService $progress): RedirectResponse
    {
        $this->authorize('view', $course);

        if (! $progress->belongsToCourse($lesson, $course)) {
            abort(404);
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
}
