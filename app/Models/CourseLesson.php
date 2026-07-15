<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\ActivityTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasUuid;

    /** @deprecated Use ActivityTypes::keys() / enabledKeys() */
    public const TYPES = ['berkas', 'video', 'url', 'pre_test', 'h5p', 'scorm', 'penugasan', 'forum', 'survey', 'post_test', 'sertifikat'];

    protected $fillable = [
        'course_module_id',
        'urutan',
        'judul',
        'tipe',
        'durasi_menit',
        'video_url',
        'file_url',
        'body',
        'show_description',
        'is_preview',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'durasi_menit' => 'integer',
            'show_description' => 'boolean',
            'is_preview' => 'boolean',
            'is_required' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'course_lesson_id');
    }

    public function normalizedType(): string
    {
        return ActivityTypes::normalize((string) $this->tipe);
    }

    public function iconClass(): string
    {
        return ActivityTypes::icon($this->tipe);
    }

    public function typeLabel(): string
    {
        return ActivityTypes::label($this->tipe);
    }

    public function typeColor(): string
    {
        return ActivityTypes::color($this->tipe);
    }

    public function sanitizedBody(): ?string
    {
        if ($this->body === null || trim($this->body) === '') {
            return null;
        }

        return strip_tags($this->body, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><a><img><table><tr><td><th><thead><tbody><span><div>');
    }

    public function externalUrl(): ?string
    {
        $type = $this->normalizedType();

        if ($type === 'video') {
            return $this->resolveMediaUrl($this->video_url);
        }

        if (in_array($type, ['berkas', 'url', 'penugasan'], true)) {
            return $this->resolveMediaUrl($this->file_url) ?: $this->resolveMediaUrl($this->video_url);
        }

        return $this->resolveMediaUrl($this->file_url) ?: $this->resolveMediaUrl($this->video_url);
    }

    public function resolveMediaUrlPublic(): ?string
    {
        return $this->resolveMediaUrl($this->video_url) ?: $this->resolveMediaUrl($this->file_url);
    }

    /**
     * True when the media should play in an HTML5 <video> element (uploaded / direct file).
     */
    public function isStreamableVideo(): bool
    {
        $raw = trim((string) ($this->video_url ?: $this->file_url));
        if ($raw === '') {
            return false;
        }

        if (preg_match('/(?:youtube\.com|youtu\.be|vimeo\.com)/i', $raw)) {
            return false;
        }

        if (! str_starts_with($raw, 'http://') && ! str_starts_with($raw, 'https://')) {
            return true;
        }

        return (bool) preg_match('/\.(mp4|webm|mov|avi|mkv|m4v)(\?|$)/i', $raw)
            || str_contains($raw, '/storage/courses/videos/');
    }

    private function resolveMediaUrl(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
    }

    public function embedVideoUrl(): ?string
    {
        $url = trim((string) ($this->video_url ?: $this->file_url));

        if ($url === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    public function isPlayable(): bool
    {
        return in_array($this->normalizedType(), ActivityTypes::enabledKeys(), true)
            || in_array($this->normalizedType(), ['pre_test', 'post_test', 'h5p', 'scorm', 'penugasan', 'forum', 'survey', 'sertifikat'], true);
    }
}
