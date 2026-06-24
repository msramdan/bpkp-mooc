<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearningProgressService
{
    public function __construct(
        private readonly CertificateService $certificates,
    ) {}

    public function enroll(User $user, Course $course): CourseEnrollment
    {
        if (! $course->is_published) {
            throw new AuthorizationException(__('Kursus ini belum dipublikasikan.'));
        }

        return DB::transaction(function () use ($user, $course): CourseEnrollment {
            $existing = CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            try {
                return CourseEnrollment::query()->create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'progress' => 0,
                    'modul_selesai' => 0,
                    'status' => 'Berlangsung',
                    'enrolled_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                return CourseEnrollment::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();
            }
        });
    }

    /** @return Collection<int, CourseLesson> */
    public function orderedLessons(Course $course): Collection
    {
        $course->loadMissing([
            'modules' => fn ($q) => $q->orderBy('urutan')->with([
                'lessons' => fn ($q) => $q->orderBy('urutan'),
            ]),
        ]);

        return $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->values();
    }

    /** @return Collection<int, string> */
    public function completedLessonIds(User $user, Course $course): Collection
    {
        $lessonIds = $this->orderedLessons($course)->pluck('id');

        if ($lessonIds->isEmpty()) {
            return collect();
        }

        return LessonCompletion::query()
            ->where('user_id', $user->id)
            ->whereIn('course_lesson_id', $lessonIds)
            ->pluck('course_lesson_id');
    }

    public function isLessonCompleted(User $user, Course $course, CourseLesson $lesson, ?Collection $completedIds = null): bool
    {
        $completedIds ??= $this->completedLessonIds($user, $course);

        return $completedIds->contains($lesson->id);
    }

    public function isLessonAccessible(User $user, Course $course, CourseLesson $lesson, ?Collection $completedIds = null): bool
    {
        if ($lesson->is_preview) {
            return true;
        }

        $ordered = $this->orderedLessons($course);
        $completedIds ??= $this->completedLessonIds($user, $course);

        if ($completedIds->isEmpty()) {
            $enrollment = CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment && $enrollment->modul_selesai > 0) {
                $lesson->loadMissing('module');

                return $lesson->module->urutan <= $enrollment->modul_selesai;
            }
        }

        foreach ($ordered as $candidate) {
            if ($candidate->id === $lesson->id) {
                return true;
            }

            if ($candidate->is_required && ! $completedIds->contains($candidate->id)) {
                return false;
            }
        }

        return false;
    }

    public function belongsToCourse(CourseLesson $lesson, Course $course): bool
    {
        $lesson->loadMissing('module');

        return $lesson->module->course_id === $course->id;
    }

    public function completeLesson(User $user, Course $course, CourseLesson $lesson): CourseEnrollment
    {
        if (! $this->belongsToCourse($lesson, $course)) {
            throw new AuthorizationException(__('Materi tidak termasuk dalam kursus ini.'));
        }

        $enrollment = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            throw new AuthorizationException(__('Anda tidak terdaftar pada kursus ini.'));
        }

        if (! $this->isLessonAccessible($user, $course, $lesson)) {
            throw new AuthorizationException(__('Materi ini masih terkunci.'));
        }

        return DB::transaction(function () use ($user, $course, $lesson, $enrollment): CourseEnrollment {
            $locked = CourseEnrollment::query()
                ->whereKey($enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            try {
                LessonCompletion::query()->create([
                    'user_id' => $user->id,
                    'course_lesson_id' => $lesson->id,
                    'completed_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Idempotent: double submit / race.
            }

            return $this->recalculateEnrollment($locked->fresh(), $course);
        });
    }

    public function recalculateEnrollment(CourseEnrollment $enrollment, ?Course $course = null): CourseEnrollment
    {
        $course ??= $enrollment->course ?? $enrollment->load('course')->course;
        $enrollment->loadMissing('user');

        $ordered = $this->orderedLessons($course);
        $required = $ordered->where('is_required', true);
        $completedIds = $this->completedLessonIds($enrollment->user, $course);

        $completedRequired = $required->filter(fn (CourseLesson $l) => $completedIds->contains($l->id))->count();
        $totalRequired = $required->count();

        $progress = $totalRequired > 0
            ? (int) min(100, round(($completedRequired / $totalRequired) * 100))
            : 0;

        $course->loadMissing(['modules.lessons']);
        $modulSelesai = 0;

        foreach ($course->modules as $module) {
            $moduleLessons = $module->lessons->where('is_required', true);

            if ($moduleLessons->isEmpty()) {
                $modulSelesai++;

                continue;
            }

            $allDone = $moduleLessons->every(fn (CourseLesson $l) => $completedIds->contains($l->id));

            if ($allDone) {
                $modulSelesai++;
            } else {
                break;
            }
        }

        $enrollment->update([
            'progress' => $progress,
            'modul_selesai' => $modulSelesai,
            'status' => $progress >= 100 ? 'Selesai' : 'Berlangsung',
        ]);

        $enrollment = $enrollment->fresh();

        if ($progress >= 100) {
            $this->certificates->issueForCompletedEnrollment(
                $enrollment->user,
                $course,
                $enrollment
            );
        }

        return $enrollment;
    }
}
