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
}
