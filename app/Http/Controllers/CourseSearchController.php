<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $isPeserta = $user->hasRole(Roles::PESERTA) && ! $user->hasRole(Roles::SUPER_ADMIN);
        $queryText = trim((string) $request->query('q', ''));

        $query = Course::query()->withCount('modules');

        if ($isPeserta) {
            $query->where('is_published', true);
        }

        if ($queryText !== '') {
            $like = '%'.$queryText.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('judul', 'like', $like)
                    ->orWhere('kategori', 'like', $like)
                    ->orWhere('instruktur', 'like', $like);
            });
        }

        $courses = $query
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return response()->json(
            $courses->map(function (Course $course) use ($isPeserta) {
                $topics = (int) ($course->modules_count ?? $course->topicsCount());

                return [
                    'id' => $course->id,
                    'judul' => $course->judul,
                    'kode' => $course->kode,
                    'kategori' => $course->kategori,
                    'topics' => $topics,
                    'topics_label' => $topics.' '.__('topik'),
                    'thumbnail' => $course->thumbnailUrl(),
                    'url' => $isPeserta
                        ? route('peserta.katalog.index', ['q' => $course->judul])
                        : route('courses.show', $course),
                ];
            })->values()
        );
    }
}
