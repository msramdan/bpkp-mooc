<?php

namespace App\Services\H5P;

use RuntimeException;

/**
 * Paket H5P yang diunggah tidak bisa diproses (rusak / struktur tidak valid).
 * Pesannya aman ditampilkan ke admin.
 */
class H5PPackageException extends RuntimeException {}
