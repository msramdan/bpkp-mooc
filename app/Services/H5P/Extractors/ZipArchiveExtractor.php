<?php

namespace App\Services\H5P\Extractors;

use App\Services\H5P\Contracts\H5PExtractorInterface;
use Exception;
use ZipArchive;
use Illuminate\Support\Facades\File;

class ZipArchiveExtractor implements H5PExtractorInterface
{
    public function extract(string $sourcePath, string $destinationPath): bool
    {
        if (!File::exists($sourcePath)) {
            throw new Exception("Source file does not exist: {$sourcePath}");
        }

        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($sourcePath) === true) {
            $zip->extractTo($destinationPath);
            $zip->close();
            return true;
        }

        throw new Exception("Failed to open zip archive: {$sourcePath}");
    }
}
