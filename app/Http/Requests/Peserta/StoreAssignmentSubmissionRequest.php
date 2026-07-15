<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('peserta kursus view') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('mooc.penugasan_max_kb', 10240);
        $mimes = implode(',', (array) config('mooc.penugasan_mimes', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip']));

        return [
            'submission_file' => ['required', 'file', 'mimes:'.$mimes, 'max:'.$maxKb],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'submission_file' => __('Hasil pengerjaan'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $maxMb = round(((int) config('mooc.penugasan_max_kb', 10240)) / 1024, 1);

        return [
            'submission_file.required' => __('Silakan unggah hasil pengerjaan.'),
            'submission_file.max' => __('Ukuran berkas maksimal :max MB.', ['max' => $maxMb]),
            'submission_file.mimes' => __('Format berkas tidak didukung. Gunakan Word, PowerPoint, PDF, atau ZIP.'),
        ];
    }
}
