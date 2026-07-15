<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CourseLesson;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class H5PAssetController extends Controller
{
    public function serve(CourseLesson $lesson, string $path = ''): BinaryFileResponse
    {
        // Cegah directory traversal
        if (str_contains($path, '..')) {
            abort(403);
        }

        // Validasi tipe aktivitas
        if ($lesson->normalizedType() !== 'h5p') {
            abort(404);
        }

        $baseDir = "courses/h5p/{$lesson->id}/extract";
        
        if (!Storage::exists("{$baseDir}/{$path}")) {
            abort(404);
        }

        $fullPath = Storage::path("{$baseDir}/{$path}");

        return response()->file($fullPath);
    }
}
