<?php

namespace App\Services\H5P;

use App\Models\CourseLesson;
use App\Services\H5P\Contracts\H5PExtractorInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class H5PService
{
    public function __construct(
        protected H5PExtractorInterface $extractor
    ) {}

    /**
     * Process an uploaded H5P package.
     *
     * Paket diekstrak & divalidasi di folder sementara dulu; paket lama (jika ada)
     * baru diganti setelah paket baru dinyatakan valid.
     *
     * @return string The storage path of the package.
     *
     * @throws Exception
     */
    public function processPackage(UploadedFile $file, CourseLesson $lesson): string
    {
        $baseDir = "courses/h5p/{$lesson->id}";
        $tmpDir = "courses/h5p/tmp-".Str::uuid();

        try {
            $packagePath = $file->storeAs($tmpDir, 'package.h5p');
            if (! $packagePath) {
                throw new Exception('Gagal menyimpan file H5P.');
            }

            $extractPath = Storage::path("{$tmpDir}/extract");
            $this->extractor->extract(Storage::path($packagePath), $extractPath);
            $this->validateStructure($extractPath);
        } catch (Exception $e) {
            Storage::deleteDirectory($tmpDir);
            throw new Exception('H5P Package tidak valid: '.$e->getMessage(), 0, $e);
        }

        Storage::deleteDirectory($baseDir);
        if (! File::moveDirectory(Storage::path($tmpDir), Storage::path($baseDir), true)) {
            Storage::deleteDirectory($tmpDir);
            throw new Exception('Gagal memindahkan paket H5P.');
        }

        return "{$baseDir}/package.h5p";
    }

    /**
     * Delete an existing H5P package and its extracted contents.
     */
    public function deletePackage(CourseLesson $lesson): void
    {
        Storage::deleteDirectory("courses/h5p/{$lesson->id}");
    }

    /**
     * Validate that the extracted package has the required files.
     *
     * @throws Exception
     */
    protected function validateStructure(string $extractPath): void
    {
        if (! File::exists("{$extractPath}/h5p.json")) {
            throw new Exception('File h5p.json tidak ditemukan di dalam package.');
        }

        if (! File::exists("{$extractPath}/content/content.json")) {
            throw new Exception('File content/content.json tidak ditemukan di dalam package.');
        }
    }
}
