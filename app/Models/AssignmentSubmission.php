<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'course_lesson_id',
        'submission_text',
        'submission_link',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function publicUrl(): ?string
    {
        if (! $this->file_path || ! Storage::disk('public')->exists($this->file_path)) {
            return null;
        }

        return asset('storage/'.$this->file_path);
    }

    public function humanSize(): string
    {
        $bytes = max(0, (int) $this->file_size);
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
