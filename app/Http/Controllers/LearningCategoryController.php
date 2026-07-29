<?php

namespace App\Http\Controllers;

use App\Http\Requests\LearningCategories\StoreLearningCategoryRequest;
use App\Http\Requests\LearningCategories\UpdateLearningCategoryRequest;
use App\Models\LearningCategory;
use DOMDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function export(): Response
    {
        $rows = LearningCategory::query()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn (string $name) => [$name])
            ->all();

        return $this->excelXmlDownload(
            'learning-categories-export-'.now()->format('Ymd-His').'.xml',
            ['Nama Kategori'],
            $rows
        );
    }

    public function downloadTemplate(): Response
    {
        return $this->excelXmlDownload(
            'template-import-kategori-kursus.xml',
            ['Nama Kategori'],
            [
                ['Audit Internal'],
                ['Manajemen Risiko'],
            ]
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xls,xml', 'max:5120'],
        ], [], [
            'import_file' => __('File import'),
        ]);

        $rows = $this->parseImportRows($request->file('import_file')->getRealPath(), (string) $request->file('import_file')->getClientOriginalExtension());

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
            'csv', 'txt' => $this->parseCsvRows($path),
            'xls', 'xml' => $this->parseExcelXmlRows($path),
            default => collect(),
        };
    }

    private function parseCsvRows(string $path): Collection
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return collect();
        }

        $rows = collect();
        $headerSkipped = false;

        while (($data = fgetcsv($handle)) !== false) {
            $name = trim((string) ($data[0] ?? ''));
            if ($name === '') {
                continue;
            }

            if (! $headerSkipped) {
                $headerSkipped = true;
                if (Str::lower($name) === 'nama kategori') {
                    continue;
                }
            }

            $rows->push($name);
        }

        fclose($handle);

        return $rows->map(fn (string $name) => trim($name))
            ->filter()
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

    private function excelXmlDownload(string $filename, array $headers, array $rows): Response
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $workbook = $xml->createElementNS('urn:schemas-microsoft-com:office:spreadsheet', 'Workbook');
        $workbook->setAttribute('xmlns:ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $xml->appendChild($workbook);

        $worksheet = $xml->createElement('Worksheet');
        $worksheet->setAttribute('ss:Name', 'Categories');
        $workbook->appendChild($worksheet);

        $table = $xml->createElement('Table');
        $worksheet->appendChild($table);

        $headerRow = $xml->createElement('Row');
        foreach ($headers as $header) {
            $cell = $xml->createElement('Cell');
            $data = $xml->createElement('Data', $header);
            $data->setAttribute('ss:Type', 'String');
            $cell->appendChild($data);
            $headerRow->appendChild($cell);
        }
        $table->appendChild($headerRow);

        foreach ($rows as $row) {
            $rowNode = $xml->createElement('Row');
            foreach ($row as $value) {
                $cell = $xml->createElement('Cell');
                $data = $xml->createElement('Data', (string) $value);
                $data->setAttribute('ss:Type', 'String');
                $cell->appendChild($data);
                $rowNode->appendChild($cell);
            }
            $table->appendChild($rowNode);
        }

        return response($xml->saveXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
