<?php

namespace App\Services\H5P;

use App\Models\CourseLesson;
use App\Services\H5P\Contracts\H5PExtractorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Exception;

class H5PService
{
    public function __construct(
        protected H5PExtractorInterface $extractor
    ) {}

    /**
     * Process an uploaded H5P package.
     *
     * @param UploadedFile $file
     * @param CourseLesson $lesson
     * @return string The storage path of the package.
     * @throws Exception
     */
    public function processPackage(UploadedFile $file, CourseLesson $lesson): string
    {
        $baseDir = "courses/h5p/{$lesson->id}";
        
        // 1. Store the uploaded file
        $packagePath = $file->storeAs($baseDir, 'package.h5p');
        if (!$packagePath) {
            throw new Exception("Gagal menyimpan file H5P.");
        }

        // 2. Extract the package
        $absoluteSourcePath = Storage::path($packagePath);
        $absoluteExtractPath = Storage::path("{$baseDir}/extract");

        try {
            $this->extractor->extract($absoluteSourcePath, $absoluteExtractPath);
            
            // 3. Validate extracted structure
            $this->validateStructure($absoluteExtractPath);
        } catch (Exception $e) {
            // Cleanup on failure
            Storage::deleteDirectory($baseDir);
            throw new Exception("H5P Package tidak valid: " . $e->getMessage());
        }

        return $packagePath;
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
     * @param string $extractPath
     * @throws Exception
     */
    protected function validateStructure(string $extractPath): void
    {
        if (!File::exists("{$extractPath}/h5p.json")) {
            throw new Exception("File h5p.json tidak ditemukan di dalam package.");
        }

        if (!File::exists("{$extractPath}/content/content.json")) {
            throw new Exception("File content/content.json tidak ditemukan di dalam package.");
        }
    }
}
