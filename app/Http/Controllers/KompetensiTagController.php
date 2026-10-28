<?php

namespace App\Http\Controllers;

use App\Models\CourseTag;
use App\Services\TnaKompetensiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Daftar tag kompetensi (read-only). Data master dikelola di Aplikasi TNA,
 * MOOC hanya menampilkan dan menghitung pemakaiannya di kursus.
 */
class KompetensiTagController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TnaKompetensiService $kompetensi) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:learning category view'),
        ];
    }

    public function index(): View
    {
        $courseCounts = CourseTag::query()
            ->selectRaw('name, count(*) as courses_count')
            ->groupBy('name')
            ->pluck('courses_count', 'name');

        $tags = collect($this->kompetensi->names())
            ->map(fn (string $name) => [
                'name' => $name,
                'courses_count' => (int) ($courseCounts[$name] ?? 0),
            ]);

        return view('kompetensi-tags.index', [
            'tags' => $tags,
            'usedCount' => $tags->where('courses_count', '>', 0)->count(),
            'isAvailable' => $tags->isNotEmpty(),
        ]);
    }

    public function refresh(): RedirectResponse
    {
        $this->kompetensi->forget();

        return $this->kompetensi->names() === []
            ? to_route('kompetensi-tags.index')->with('error', __('Data kompetensi dari Aplikasi TNA belum dapat dimuat.'))
            : to_route('kompetensi-tags.index')->with('success', __('Data kompetensi berhasil dimuat ulang dari Aplikasi TNA.'));
    }
}
