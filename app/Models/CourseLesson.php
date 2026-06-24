<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLesson extends Model
{
    use HasUuid;

    public const TYPES = ['video', 'dokumen', 'kuis', 'reading', 'live'];

    protected $fillable = [
        'course_module_id',
        'urutan',
        'judul',
        'tipe',
        'durasi_menit',
        'video_url',
        'file_url',
        'body',
        'is_preview',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'durasi_menit' => 'integer',
            'is_preview' => 'boolean',
            'is_required' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function iconClass(): string
    {
        return match ($this->tipe) {
            'video' => 'bi-play-circle',
            'dokumen' => 'bi-file-earmark-pdf',
            'kuis' => 'bi-patch-question',
            'reading' => 'bi-journal-text',
            'live' => 'bi-camera-video',
            default => 'bi-circle',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->tipe) {
            'video' => __('Video'),
            'dokumen' => __('Dokumen'),
            'kuis' => __('Kuis'),
            'reading' => __('Bacaan'),
            'live' => __('Live session'),
            default => ucfirst($this->tipe),
        };
    }

    public function sanitizedBody(): ?string
    {
        if ($this->body === null || trim($this->body) === '') {
            return null;
        }

        return strip_tags($this->body, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><a><img><table><tr><td><th><thead><tbody><span><div>');
    }

    public function embedVideoUrl(): ?string
    {
        if ($this->video_url === null || $this->video_url === '') {
            return null;
        }

        $url = $this->video_url;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }

    public function isPlayable(): bool
    {
        return in_array($this->tipe, ['video', 'dokumen', 'reading', 'kuis', 'live'], true);
    }
}
