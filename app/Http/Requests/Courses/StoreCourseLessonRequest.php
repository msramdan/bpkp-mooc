<?php

namespace App\Http\Requests\Courses;

use App\Models\CourseLesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course edit') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'tipe' => ['required', Rule::in(CourseLesson::TYPES)],
            'durasi_menit' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'file_url' => ['nullable', 'string', 'max:2048'],
            'body' => ['nullable', 'string'],
            'is_preview' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }
}
