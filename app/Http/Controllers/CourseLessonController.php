<?php

namespace App\Http\Controllers;

use App\Http\Requests\Courses\StoreCourseLessonRequest;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CourseLessonController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course edit'),
        ];
    }

    public function store(StoreCourseLessonRequest $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);

        DB::transaction(function () use ($request, $module): void {
            $nextUrutan = (int) $module->lessons()->max('urutan') + 1;

            $module->lessons()->create([
                'urutan' => max(1, $nextUrutan),
                'judul' => $request->validated('judul'),
                'tipe' => $request->validated('tipe'),
                'durasi_menit' => $request->validated('durasi_menit') ?? 0,
                'video_url' => $request->validated('video_url'),
                'file_url' => $request->validated('file_url'),
                'body' => $request->validated('body'),
                'is_preview' => $request->boolean('is_preview'),
                'is_required' => $request->boolean('is_required', true),
            ]);
        });

        return back()->with('success', __('Materi berhasil ditambahkan.'));
    }

    public function update(StoreCourseLessonRequest $request, Course $course, CourseModule $module, CourseLesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);
        $this->assertLessonBelongsToModule($lesson, $module);

        $lesson->update([
            'judul' => $request->validated('judul'),
            'tipe' => $request->validated('tipe'),
            'durasi_menit' => $request->validated('durasi_menit') ?? 0,
            'video_url' => $request->validated('video_url'),
            'file_url' => $request->validated('file_url'),
            'body' => $request->validated('body'),
            'is_preview' => $request->boolean('is_preview'),
            'is_required' => $request->boolean('is_required', true),
        ]);

        return back()->with('success', __('Materi berhasil diperbarui.'));
    }

    public function destroy(Course $course, CourseModule $module, CourseLesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);
        $this->assertLessonBelongsToModule($lesson, $module);

        DB::transaction(function () use ($module, $lesson): void {
            $lesson->delete();

            $module->lessons()
                ->orderBy('urutan')
                ->get()
                ->each(function (CourseLesson $item, int $index): void {
                    $item->update(['urutan' => $index + 1]);
                });
        });

        return back()->with('success', __('Materi berhasil dihapus.'));
    }

    private function assertModuleBelongsToCourse(CourseModule $module, Course $course): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }

    private function assertLessonBelongsToModule(CourseLesson $lesson, CourseModule $module): void
    {
        if ($lesson->course_module_id !== $module->id) {
            abort(404);
        }
    }
}
