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

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
        ];

        $headers = [];
        if (array_key_exists($extension, $mimeTypes)) {
            $headers['Content-Type'] = $mimeTypes[$extension];
        }

        return response()->file($fullPath, $headers);
    }
}
