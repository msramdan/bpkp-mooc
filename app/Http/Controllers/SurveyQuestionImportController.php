<?php

namespace App\Http\Controllers;

use App\Exports\PilihanGanda\TemplateExport;
use App\Models\Survey;
use App\Services\PilihanGandaImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import soal Pilihan Ganda dari Excel: unduh template → unggah → pratinjau → simpan.
 */
class SurveyQuestionImportController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PilihanGandaImporter $importer) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:survey edit'),
        ];
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new TemplateExport, 'template-import-soal-pilihan-ganda.xlsx');
    }

    public function preview(Request $request, Survey $survey): View|RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'import_file.required' => __('Pilih file Excel soal terlebih dahulu.'),
            'import_file.mimes' => __('File harus berformat Excel (.xlsx atau .xls). Gunakan template yang disediakan.'),
            'import_file.max' => __('Ukuran file maksimal 5 MB.'),
        ]);

        try {
            $result = $this->importer->parse($request->file('import_file')->getRealPath());
        } catch (\Throwable) {
            return to_route('surveys.builder', $survey)
                ->with('error', __('File tidak dapat dibaca. Pastikan memakai template Excel dari halaman ini.'));
        }

        if ($result['rows'] === []) {
            return to_route('surveys.builder', $survey)
                ->with('error', __('Belum ada soal di file. Isi soal mulai baris ke-4 pada sheet "Soal PG", lalu unggah ulang.'));
        }

        $request->session()->put($this->sessionKey($survey), $result['rows']);

        return view('surveys.import-pg-preview', [
            'survey' => $survey,
            'rows' => $result['rows'],
            'skippedExamples' => $result['skipped_examples'],
            'validCount' => collect($result['rows'])->where('errors', [])->count(),
            'fileName' => $request->file('import_file')->getClientOriginalName(),
        ]);
    }

    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $rows = $request->session()->pull($this->sessionKey($survey));

        if (! is_array($rows) || $rows === []) {
            return to_route('surveys.builder', $survey)
                ->with('error', __('Data pratinjau sudah tidak tersedia. Silakan unggah ulang file soal.'));
        }

        $imported = $this->importer->import($survey, $rows);

        return to_route('surveys.builder', $survey)
            ->with('success', __(':count soal pilihan ganda berhasil diimpor.', ['count' => $imported]));
    }

    public function cancel(Request $request, Survey $survey): RedirectResponse
    {
        $request->session()->forget($this->sessionKey($survey));

        return to_route('surveys.builder', $survey);
    }

    private function sessionKey(Survey $survey): string
    {
        return 'survey-import-pg.'.$survey->id;
    }
}
