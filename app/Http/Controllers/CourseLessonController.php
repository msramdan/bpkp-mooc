<?php

namespace App\Http\Controllers;

use App\Http\Requests\Courses\StoreCourseLessonRequest;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Services\H5P\H5PService;
use App\Support\ActivityTypes;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            $data = $request->safe()->except(['berkas_file', 'video_file', 'deskripsi']);
            $body = $request->input('body') ?? $request->input('deskripsi');
            $tipe = ActivityTypes::normalize((string) $data['tipe']);

            $fileUrl = null;
            $videoUrl = null;

            if ($tipe === 'berkas' || $tipe === 'penugasan') {
                $dir = $tipe === 'penugasan' ? 'courses/assignments' : 'courses/activities';
                $fileUrl = $this->storeUpload($request, 'berkas_file', $dir);
            } elseif ($tipe === 'video') {
                $videoUrl = $this->storeUpload($request, 'video_file', 'courses/videos');
            } elseif ($tipe === 'url') {
                $fileUrl = $data['file_url'] ?? null;
            }

            $lesson = $module->lessons()->create([
                'urutan' => max(1, $nextUrutan),
                'judul' => $data['judul'],
                'tipe' => $tipe,
                'durasi_menit' => $data['durasi_menit'] ?? 0,
                'video_url' => $videoUrl,
                'file_url' => $fileUrl,
                'survey_id' => $tipe === 'survey' ? ($data['survey_id'] ?? null) : null,
                'body' => $body,
                'show_description' => true,
                'is_preview' => $request->boolean('is_preview'),
                'is_required' => $request->boolean('is_required', true),
            ]);

            if ($tipe === 'h5p' && $request->hasFile('berkas_file')) {
                $h5pService = app(H5PService::class);
                $lesson->update([
                    'file_url' => $h5pService->processPackage($request->file('berkas_file'), $lesson)
                ]);
            }
        });

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'modules'])
            ->with('success', __('Aktivitas berhasil ditambahkan.'));
    }

    public function update(StoreCourseLessonRequest $request, Course $course, CourseModule $module, CourseLesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);
        $this->assertLessonBelongsToModule($lesson, $module);

        $data = $request->safe()->except(['berkas_file', 'video_file', 'deskripsi']);
        $body = $request->input('body') ?? $request->input('deskripsi') ?? $lesson->body;
        $tipe = ActivityTypes::normalize((string) ($data['tipe'] ?? $lesson->tipe));

        $payload = [
            'judul' => $data['judul'],
            'tipe' => $tipe,
            'durasi_menit' => $data['durasi_menit'] ?? 0,
            'survey_id' => $tipe === 'survey' ? ($data['survey_id'] ?? null) : null,
            'body' => $body,
            'show_description' => true,
            'is_preview' => $request->boolean('is_preview'),
            'is_required' => $request->boolean('is_required', true),
        ];

        if ($tipe === 'video') {
            if ($request->hasFile('video_file')) {
                $this->deleteStoredPath($lesson->video_url, 'courses/videos/');
                $payload['video_url'] = $this->storeUpload($request, 'video_file', 'courses/videos');
            }
            $payload['file_url'] = null;
        } elseif ($tipe === 'url') {
            $payload['file_url'] = $data['file_url'] ?? $lesson->file_url;
            if ($lesson->video_url) {
                $this->deleteStoredPath($lesson->video_url, 'courses/videos/');
            }
            $payload['video_url'] = null;
        } elseif ($tipe === 'berkas' || $tipe === 'penugasan') {
            $dir = $tipe === 'penugasan' ? 'courses/assignments' : 'courses/activities';
            if ($request->hasFile('berkas_file')) {
                $this->deleteStoredPath($lesson->file_url, 'courses/activities/');
                $this->deleteStoredPath($lesson->file_url, 'courses/assignments/');
                $payload['file_url'] = $this->storeUpload($request, 'berkas_file', $dir);
            }
            if ($lesson->video_url) {
                $this->deleteStoredPath($lesson->video_url, 'courses/videos/');
            }
            $payload['video_url'] = null;
        } elseif ($tipe === 'h5p') {
            if ($request->hasFile('berkas_file')) {
                $h5pService = app(H5PService::class);
                $h5pService->deletePackage($lesson);
                $payload['file_url'] = $h5pService->processPackage($request->file('berkas_file'), $lesson);
            }
            if ($lesson->video_url) {
                $this->deleteStoredPath($lesson->video_url, 'courses/videos/');
            }
            $payload['video_url'] = null;
        }

        $lesson->update($payload);

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'modules'])
            ->with('success', __('Aktivitas berhasil diperbarui.'));
    }

    public function destroy(Course $course, CourseModule $module, CourseLesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        $this->assertModuleBelongsToCourse($module, $course);
        $this->assertLessonBelongsToModule($lesson, $module);

        DB::transaction(function () use ($module, $lesson): void {
            $this->deleteStoredPath($lesson->file_url, 'courses/activities/');
            $this->deleteStoredPath($lesson->file_url, 'courses/assignments/');
            if ($lesson->tipe === 'h5p') {
                app(H5PService::class)->deletePackage($lesson);
            }
            $this->deleteStoredPath($lesson->video_url, 'courses/videos/');
            $lesson->delete();

            $module->lessons()
                ->orderBy('urutan')
                ->get()
                ->each(function (CourseLesson $item, int $index): void {
                    $item->update(['urutan' => $index + 1]);
                });
        });

        return redirect()
            ->route('courses.show', [$course, 'tab' => 'modules'])
            ->with('success', __('Aktivitas berhasil dihapus.'));
    }

    private function storeUpload(Request $request, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $path = $request->file($field)->store($directory, 'public');

        return $path ? Storage::disk('public')->url($path) : null;
    }

    private function deleteStoredPath(?string $url, string $expectedPrefix): void
    {
        if ($url === null || $url === '') {
            return;
        }

        $prefix = '/storage/';
        $pos = strpos($url, $prefix);
        if ($pos === false) {
            return;
        }

        $relative = ltrim(substr($url, $pos + strlen($prefix)), '/');
        if ($relative !== '' && str_starts_with($relative, $expectedPrefix) && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
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
