<?php

namespace App\Http\Controllers;

use App\Http\Requests\Courses\StoreCourseModuleRequest;
use App\Models\Course;
use App\Models\CourseModule;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CourseModuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course edit'),
        ];
    }

    public function store(StoreCourseModuleRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        DB::transaction(function () use ($request, $course): void {
            $nextUrutan = (int) $course->modules()->max('urutan') + 1;

            $course->modules()->create([
                'urutan' => max(1, $nextUrutan),
                'judul' => $request->validated('judul'),
                'deskripsi' => $request->validated('deskripsi'),
                'durasi_menit' => $request->validated('durasi_menit') ?? 0,
            ]);

            $course->update(['modul_total' => $course->modules()->count()]);
        });

        return back()->with('success', __('Modul berhasil ditambahkan.'));
    }

    public function update(StoreCourseModuleRequest $request, Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);

        $module->update([
            'judul' => $request->validated('judul'),
            'deskripsi' => $request->validated('deskripsi'),
            'durasi_menit' => $request->validated('durasi_menit') ?? 0,
        ]);

        return back()->with('success', __('Modul berhasil diperbarui.'));
    }

    public function destroy(Course $course, CourseModule $module): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);

        DB::transaction(function () use ($course, $module): void {
            $module->delete();

            $course->modules()
                ->orderBy('urutan')
                ->get()
                ->each(function (CourseModule $item, int $index): void {
                    $item->update(['urutan' => $index + 1]);
                });

            $course->update(['modul_total' => max(1, $course->modules()->count())]);
        });

        return back()->with('success', __('Modul berhasil dihapus.'));
    }

    private function assertModuleBelongsToCourse(CourseModule $module, Course $course): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }
}
