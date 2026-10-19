<?php

namespace App\Http\Controllers;

use App\Exports\CourseEnrollmentExport;
use App\Http\Requests\LearningCategories\StoreLearningCategoryRequest;
use App\Http\Requests\LearningCategories\UpdateLearningCategoryRequest;
use App\Models\LearningCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class LearningCategoryController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:learning category view', only: ['index', 'show']),
            new Middleware('permission:learning category create', only: ['create', 'store']),
            new Middleware('permission:learning category edit', only: ['edit', 'update']),
            new Middleware('permission:learning category delete', only: ['destroy']),
            new Middleware('permission:learning category view', only: ['export', 'downloadTemplate']),
            new Middleware('permission:learning category create', only: ['import']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(LearningCategory::query())
                ->addColumn('action', fn (LearningCategory $learningCategory) => view('learning-categories.include.action', ['model' => $learningCategory])->render())
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('learning-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return to_route('learning-categories.index');
    }

    public function export(): BinaryFileResponse
    {
        $rows = LearningCategory::query()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn (string $name) => [$name])
            ->all();

        return Excel::download(
            new CourseEnrollmentExport($rows, ['Nama Kategori']),
            'learning-categories-export-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new CourseEnrollmentExport(
                [
                    ['Audit Internal'],
                    ['Manajemen Risiko'],
                ],
                ['Nama Kategori']
            ),
            'template-import-kategori-kursus.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt,xml', 'max:5120'],
        ], [], [
            'import_file' => __('File import'),
        ]);

        try {
            $rows = $this->parseImportRows($request->file('import_file')->getRealPath(), (string) $request->file('import_file')->getClientOriginalExtension());
        } catch (\Throwable) {
            return to_route('learning-categories.index')
                ->with('error', __('File import tidak dapat dibaca. Gunakan template Excel dari halaman ini.'));
        }

        if ($rows->isEmpty()) {
            return to_route('learning-categories.index')
                ->with('error', __('Tidak ada data kategori yang bisa diimpor.'));
        }

        $imported = 0;

        DB::transaction(function () use ($rows, &$imported): void {
            foreach ($rows as $name) {
                $category = LearningCategory::firstOrCreate(['name' => $name]);
                if ($category->wasRecentlyCreated) {
                    $imported++;
                }
            }
        });

        return to_route('learning-categories.index')->with(
            'success',
            __('Import kategori selesai. :count kategori baru ditambahkan.', ['count' => $imported])
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearningCategoryRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            LearningCategory::create($request->validated());

            return to_route('learning-categories.index')->with('success', __('The learning category was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LearningCategory $learningCategory): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'name' => $learningCategory->name,
                'created_at' => $learningCategory->created_at?->format('Y-m-d H:i'),
                'updated_at' => $learningCategory->updated_at?->format('Y-m-d H:i'),
                'edit_url' => null,
            ]);
        }

        return to_route('learning-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningCategory $learningCategory): RedirectResponse
    {
        unset($learningCategory);

        return to_route('learning-categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearningCategoryRequest $request, LearningCategory $learningCategory): RedirectResponse
    {
        return DB::transaction(function () use ($request, $learningCategory): RedirectResponse {
            $learningCategory->update($request->validated());

            return to_route('learning-categories.index')->with('success', __('The learning category was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningCategory $learningCategory): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($learningCategory): RedirectResponse {
                $learningCategory->delete();

                return to_route('learning-categories.index')->with('success', __('The learning category was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route('learning-categories.index')->with('error', __("The learning category can't be deleted because it's related to another table."));
        }
    }

    private function parseImportRows(string $path, string $extension): Collection
    {
        $ext = strtolower($extension);

        return match ($ext) {
            'xml' => $this->parseExcelXmlRows($path),
            default => $this->parseSpreadsheetRows($path),
        };
    }

    private function parseSpreadsheetRows(string $path): Collection
    {
        $sheet = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
        $headerSkipped = false;

        return collect($sheet)
            ->map(fn (array $row) => trim((string) ($row[0] ?? '')))
            ->filter()
            ->reject(function (string $name) use (&$headerSkipped) {
                if ($headerSkipped) {
                    return false;
                }

                $headerSkipped = true;

                return Str::lower($name) === 'nama kategori';
            })
            ->unique(fn (string $name) => Str::lower($name))
            ->values();
    }

    private function parseExcelXmlRows(string $path): Collection
    {
        $xml = @simplexml_load_file($path);
        if ($xml === false) {
            return collect();
        }

        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $rows = collect($xml->xpath('//ss:Worksheet/ss:Table/ss:Row') ?: []);

        return $rows->skip(1)
            ->map(function ($row) {
                $cells = $row->xpath('ss:Cell/ss:Data');
                return trim((string) ($cells[0] ?? ''));
            })
            ->filter()
            ->unique(fn (string $name) => Str::lower($name))
            ->values();
    }
}
