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

    /**
     * Ringkasan topik & aktivitas selesai/belum untuk 1 peserta (admin modal).
     *
     * @return array{
     *     participant: array{name: string, email: string},
     *     progress: int,
     *     status: string,
     *     status_label: string,
     *     modules_done: int,
     *     modules_total: int,
     *     activities_done: int,
     *     activities_total: int,
     *     modules: list<array{
     *         id: string,
     *         urutan: int,
     *         title: string,
     *         is_complete: bool,
     *         done_count: int,
     *         total_count: int,
     *         activities: list<array{
     *             id: string,
     *             urutan: int,
     *             title: string,
     *             type: string,
     *             type_label: string,
     *             icon: string,
     *             is_required: bool,
     *             is_complete: bool,
     *             completed_at: string|null,
     *             completed_at_iso: string|null
     *         }>
     *     }>
     * }
     */
    public function progressBreakdown(User $user, Course $course, ?CourseEnrollment $enrollment = null): array
    {
        $enrollment ??= CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $course->loadMissing([
            'modules' => fn ($q) => $q->orderBy('urutan')->with([
                'lessons' => fn ($q) => $q->orderBy('urutan'),
            ]),
        ]);

        $lessonIds = $course->modules->flatMap(fn ($m) => $m->lessons->pluck('id'))->all();

        $completions = $lessonIds === []
            ? collect()
            : LessonCompletion::query()
                ->where('user_id', $user->id)
                ->whereIn('course_lesson_id', $lessonIds)
                ->get()
                ->keyBy('course_lesson_id');

        $modulesOut = [];
        $activitiesDone = 0;
        $activitiesTotal = 0;
        $modulesDone = 0;

        foreach ($course->modules as $module) {
            $activities = [];
            $doneCount = 0;
            $totalCount = $module->lessons->count();

            foreach ($module->lessons as $lesson) {
                $completion = $completions->get($lesson->id);
                $isComplete = $completion !== null;

                if ($isComplete) {
                    $doneCount++;
                    $activitiesDone++;
                }

                $activitiesTotal++;

                $activities[] = [
                    'id' => (string) $lesson->id,
                    'urutan' => (int) $lesson->urutan,
                    'title' => (string) $lesson->judul,
                    'type' => $lesson->normalizedType(),
                    'type_label' => $lesson->typeLabel(),
                    'icon' => $lesson->iconClass(),
                    'is_required' => (bool) $lesson->is_required,
                    'is_complete' => $isComplete,
                    'completed_at' => $completion?->completed_at?->timezone(config('app.timezone'))->translatedFormat('d M Y H:i'),
                    'completed_at_iso' => $completion?->completed_at?->toIso8601String(),
                ];
            }

            $requiredLessons = $module->lessons->where('is_required', true);
            // Samakan dengan recalculateEnrollment: topik dianggap selesai
            // bila semua aktivitas wajib selesai (atau tidak ada aktivitas wajib).
            $moduleComplete = $requiredLessons->isEmpty()
                || $requiredLessons->every(fn (CourseLesson $l) => $completions->has($l->id));

            if ($moduleComplete) {
                $modulesDone++;
            }

            $modulesOut[] = [
                'id' => (string) $module->id,
                'urutan' => (int) $module->urutan,
                'title' => (string) $module->judul,
                'is_complete' => $moduleComplete,
                'done_count' => $doneCount,
                'total_count' => $totalCount,
                'activities' => $activities,
            ];
        }

        $status = $enrollment?->status ?? 'Berlangsung';

        return [
            'participant' => [
                'name' => (string) $user->name,
                'email' => (string) $user->email,
            ],
            'progress' => (int) ($enrollment?->progress ?? 0),
            'status' => $status,
            'status_label' => __($status),
            'modules_done' => (int) ($enrollment?->modul_selesai ?? $modulesDone),
            'modules_total' => (int) ($course->modul_total ?: $course->modules->count()),
            'activities_done' => $activitiesDone,
            'activities_total' => $activitiesTotal,
            'modules' => $modulesOut,
        ];
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
