<?php

namespace App\Http\Controllers;

use App\Support\Roles;
use App\Http\Requests\Courses\StoreCourseRequest;
use App\Http\Requests\Courses\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course view', only: ['index', 'show']),
            new Middleware('permission:course create', only: ['create', 'store']),
            new Middleware('permission:course edit', only: ['edit', 'update']),
            new Middleware('permission:course delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Course::query()->with(['tags'])->withCount(['enrollments', 'modules']);

            if ($request->filled('kategori')) {
                $query->where('kategori', (string) $request->input('kategori'));
            }

            if ($request->filled('instansi')) {
                $query->where('instansi', (string) $request->input('instansi'));
            }

            $published = $request->input('published');
            if ($published === '1' || $published === '0') {
                $query->where('is_published', $published === '1');
            }

            return DataTables::eloquent($query)
                ->addColumn('card', fn (Course $course) => view('courses.partials.grid-card', compact('course'))->render())
                ->addColumn('published_label', fn (Course $course) => $course->is_published
                    ? '<span class="badge bg-success-transparent">'.__('Dipublikasikan').'</span>'
                    : '<span class="badge bg-secondary-transparent">'.__('Draft').'</span>')
                ->addColumn('action', fn (Course $course) => view('courses.include.action', ['model' => $course])->render())
                ->rawColumns(['card', 'published_label', 'action'])
                ->toJson();
        }

        return view('courses.index', [
            'learningCategories' => \App\Models\LearningCategory::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function create(): RedirectResponse
    {
        return to_route('courses.index');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            $data = $request->safe()->except(['thumbnail_file', 'tag_ids']);
            $data['kode'] = $this->uniqueCourseCode($data['judul']);
            $data['slug'] = $this->uniqueCourseSlug($data['judul'], $data['kode']);
            $data['is_published'] = $request->boolean('is_published');
            $data['is_forum_open'] = $request->boolean('is_forum_open');
            $data['ends_at_enabled'] = $request->boolean('ends_at_enabled');
            if (! $data['ends_at_enabled']) {
                $data['ends_at'] = null;
            }
            $data['instruktur'] = $data['instruktur'] ?? 'Tim Pengajar BPKP';
            $data['durasi_jam'] = $data['durasi_jam'] ?? 0;
            $data['modul_total'] = 0;
            $data['level'] = $data['level'] ?? 'Pemula';
            $data['rating'] = $data['rating'] ?? 0;
            $data['thumbnail'] = $this->storeThumbnail($request) ?? null;

            $course = Course::create($data);
            $course->tags()->sync($request->input('tag_ids', []));

            return to_route('courses.show', [$course, 'tab' => 'modules'])->with('success', __('Kursus berhasil dibuat. Tambahkan topik untuk mulai menyusun aktivitas.'));
        });
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $search = trim((string) $request->query('q', ''));
        $activeTab = in_array($request->query('tab'), ['info', 'modules', 'peserta', 'forum'], true)
            ? (string) $request->query('tab')
            : (($search !== '' || $request->has('page')) ? 'peserta' : 'info');

        $course->loadCount(['modules', 'enrollments', 'forumThreads']);
        $course->load(['tags:id,name']);

        // Heavy module tree only when needed.
        if ($activeTab === 'modules') {
            $course->load([
                'modules' => fn ($q) => $q->orderBy('urutan')->with([
                    'lessons' => fn ($l) => $l->orderBy('urutan'),
                ]),
            ]);
        }

        $forumThreads = collect();
        if ($activeTab === 'forum') {
            $forumThreads = $course->forumThreads()
                ->with([
                    'user:id,name,email',
                    'replies' => fn ($query) => $query->with('user:id,name,email')->oldest(),
                ])
                ->get();
        }

        $enrollments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        $pesertaUsers = collect();

        if ($activeTab === 'peserta') {
            $enrollments = $course->enrollments()
                ->select([
                    'id', 'user_id', 'course_id', 'progress', 'modul_selesai',
                    'status', 'enrolled_at',
                ])
                ->with(['user:id,name,email'])
                ->when($search !== '', function ($query) use ($search) {
                    $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
                    $query->whereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
                })
                ->latest('enrolled_at')
                ->paginate(25)
                ->withQueryString();

            if (auth()->user()?->can('course enrollment manage')) {
                // Dropdown diisi via AJAX search (avoid limit 500 / missing names).
                $pesertaUsers = collect();
            }
        }

        $surveys = Survey::where('is_active', true)->orderBy('title')->get(['id', 'title']);
        $certificateTemplates = \App\Models\CertificateTemplate::orderBy('title')->get();

        return view('courses.show', [
            'course' => $course,
            'enrollments' => $enrollments,
            'enrollmentsCount' => (int) $course->enrollments_count,
            'pesertaUsers' => $pesertaUsers,
            'pesertaSearch' => $search,
            'activeTab' => $activeTab,
            'surveys' => $surveys,
            'certificateTemplates' => $certificateTemplates,
            'forumThreads' => $forumThreads,
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
            $data = $request->safe()->except(['thumbnail_file', 'remove_thumbnail', 'tag_ids']);
            $data['slug'] = $this->uniqueCourseSlug($data['judul'], $course->kode, $course->id);
            $data['is_published'] = $request->boolean('is_published');
            $data['is_forum_open'] = $request->boolean('is_forum_open');
            $data['ends_at_enabled'] = $request->boolean('ends_at_enabled');
            if (! $data['ends_at_enabled']) {
                $data['ends_at'] = null;
            }

            if ($request->boolean('remove_thumbnail') && ! $request->hasFile('thumbnail_file')) {
                $this->deleteStoredThumbnail($course->thumbnail);
                $data['thumbnail'] = null;
            }

            if ($request->hasFile('thumbnail_file')) {
                $this->deleteStoredThumbnail($course->thumbnail);
                $data['thumbnail'] = $this->storeThumbnail($request);
            }

            $course->update($data);
            $course->tags()->sync($request->input('tag_ids', []));

            return to_route('courses.show', [$course, 'tab' => 'info'])->with('success', __('Kursus berhasil diperbarui.'));
        });
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        try {
            return DB::transaction(function () use ($course): RedirectResponse {
                $this->deleteStoredThumbnail($course->thumbnail);
                $course->delete();

                return to_route('courses.index')->with('success', __('Kursus berhasil dihapus.'));
            });
        } catch (\Exception) {
            return to_route('courses.index')->with('error', __('Kursus tidak dapat dihapus karena masih memiliki data terkait.'));
        }
    }

    private function storeThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail_file')) {
            return null;
        }

        // Store relative disk path so URLs resolve against the current app host.
        return $request->file('thumbnail_file')->store('courses/thumbnails', 'public') ?: null;
    }

    private function deleteStoredThumbnail(?string $thumbnail): void
    {
        $relative = $this->thumbnailRelativePath($thumbnail);
        if ($relative === null) {
            return;
        }

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }

    private function thumbnailRelativePath(?string $thumbnail): ?string
    {
        if ($thumbnail === null || trim($thumbnail) === '') {
            return null;
        }

        $thumbnail = trim($thumbnail);
        $prefix = '/storage/';
        $pos = strpos($thumbnail, $prefix);
        if ($pos !== false) {
            $thumbnail = ltrim(substr($thumbnail, $pos + strlen($prefix)), '/');
        }

        if (! str_starts_with($thumbnail, 'courses/thumbnails/')) {
            return null;
        }

        return $thumbnail;
    }

    private function uniqueCourseCode(string $judul): string
    {
        $base = Str::upper(Str::limit(Str::slug($judul, ''), 12, ''));
        if ($base === '') {
            $base = 'KURSUS';
        }

        $code = $base;
        $i = 1;
        while (Course::query()->where('kode', $code)->exists()) {
            $suffix = (string) $i;
            $code = Str::limit($base, 20 - strlen($suffix), '').$suffix;
            $i++;
        }

        return Str::limit($code, 20, '');
    }

    private function uniqueCourseSlug(string $judul, string $kode, ?string $ignoreId = null): string
    {
        $base = Str::slug($judul);
        if ($base === '') {
            $base = Str::slug($kode) ?: 'kursus';
        }

        $slug = $base;
        $i = 1;
        while (
            Course::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
