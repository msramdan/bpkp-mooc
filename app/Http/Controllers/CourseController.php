<?php

namespace App\Http\Controllers;

use App\Http\Requests\Courses\StoreCourseRequest;
use App\Http\Requests\Courses\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
            new Middleware('permission:course view', only: ['index', 'show']),
            new Middleware('permission:course create', only: ['create', 'store']),
            new Middleware('permission:course edit', only: ['edit', 'update']),
            new Middleware('permission:course delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(
                Course::query()->withCount(['enrollments', 'modules'])
            )
                ->addColumn('published_label', fn (Course $course) => $course->is_published
                    ? '<span class="badge bg-success-transparent">'.__('Dipublikasikan').'</span>'
                    : '<span class="badge bg-secondary-transparent">'.__('Draft').'</span>')
                ->addColumn('action', fn (Course $course) => view('courses.include.action', ['model' => $course])->render())
                ->rawColumns(['published_label', 'action'])
                ->toJson();
        }

        return view('courses.index');
    }

    public function create(): RedirectResponse
    {
        return to_route('courses.index');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['kode'].'-'.$data['judul']);
            $data['is_published'] = $request->boolean('is_published');
            $data['rating'] = $data['rating'] ?? 0;

            Course::create($data);

            return to_route('courses.index')->with('success', __('Kursus berhasil dibuat.'));
        });
    }

    public function show(Course $course): View
    {
        $this->authorize('view', $course);

        $course->load([
            'modules' => fn ($q) => $q->orderBy('urutan')->withCount('lessons'),
            'enrollments.user',
        ]);

        $pesertaUsers = \App\Models\User::role('peserta')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $enrolledUserIds = $course->enrollments->pluck('user_id');

        return view('courses.show', [
            'course' => $course,
            'pesertaUsers' => $pesertaUsers,
            'enrolledUserIds' => $enrolledUserIds,
        ]);
    }

    public function edit(Course $course): RedirectResponse
    {
        unset($course);

        return to_route('courses.index');
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        return DB::transaction(function () use ($request, $course): RedirectResponse {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['kode'].'-'.$data['judul']);
            $data['is_published'] = $request->boolean('is_published');

            $course->update($data);

            return to_route('courses.show', $course)->with('success', __('Kursus berhasil diperbarui.'));
        });
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        try {
            return DB::transaction(function () use ($course): RedirectResponse {
                $course->delete();

                return to_route('courses.index')->with('success', __('Kursus berhasil dihapus.'));
            });
        } catch (\Exception) {
            return to_route('courses.index')->with('error', __('Kursus tidak dapat dihapus karena masih memiliki data terkait.'));
        }
    }
}
