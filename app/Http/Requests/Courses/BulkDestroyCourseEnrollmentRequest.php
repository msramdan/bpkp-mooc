<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course enrollment manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'enrollment_ids' => __('Peserta'),
            'enrollment_ids.*' => __('Peserta'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'enrollment_ids.required' => __('Pilih minimal satu peserta.'),
            'enrollment_ids.min' => __('Pilih minimal satu peserta.'),
        ];
    }
}
