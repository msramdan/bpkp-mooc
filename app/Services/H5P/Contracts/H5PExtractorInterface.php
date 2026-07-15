<?php

namespace App\Services\H5P\Contracts;

interface H5PExtractorInterface
{
    /**
     * Extract a package to a destination directory.
     *
     * @param string $sourcePath The absolute path to the uploaded package.
     * @param string $destinationPath The absolute path to extract to.
     * @return bool True if successful, false otherwise.
     * @throws \Exception If extraction fails.
     */
    public function extract(string $sourcePath, string $destinationPath): bool;
}
