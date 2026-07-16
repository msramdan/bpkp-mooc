<?php

namespace App\Http\Requests\Courses;

use App\Support\ActivityTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_preview' => $this->boolean('is_preview'),
            'is_required' => $this->boolean('is_required', true),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tipe = ActivityTypes::normalize((string) $this->input('tipe'));
        $isUpdate = $this->route('lesson') !== null;
        $berkasMaxKb = (int) config('mooc.berkas_max_kb', 10240);
        $berkasMimes = implode(',', (array) config('mooc.berkas_mimes', ['pdf']));
        $penugasanMaxKb = (int) config('mooc.penugasan_max_kb', 10240);
        $penugasanMimes = implode(',', (array) config('mooc.penugasan_mimes', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip']));
        $videoMaxKb = (int) config('mooc.video_max_kb', 51200);
        $videoMimes = implode(',', (array) config('mooc.video_mimes', ['mp4', 'webm']));

        $lesson = $isUpdate ? $this->route('lesson') : null;
        $hasExistingBerkas = $isUpdate && filled(optional($lesson)->file_url);
        $hasExistingVideo = $isUpdate && filled(optional($lesson)->video_url);

        $h5pMaxKb = (int) config('mooc.h5p_max_kb', 204800);
        $fileMaxKb = match ($tipe) {
            'penugasan' => $penugasanMaxKb,
            'h5p' => $h5pMaxKb,
            default => $berkasMaxKb,
        };
        $fileMimes = match ($tipe) {
            'penugasan' => $penugasanMimes,
            'h5p' => 'h5p,zip',
            default => $berkasMimes,
        };

        return [
            'judul' => ['required', 'string', 'max:255'],
            'tipe' => [
                'required',
                Rule::in($isUpdate
                    ? array_values(array_unique([...ActivityTypes::keys(), ...array_keys(ActivityTypes::LEGACY_MAP)]))
                    : ActivityTypes::enabledKeys()),
            ],
            'deskripsi' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'durasi_menit' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'file_url' => [
                Rule::requiredIf(fn () => in_array($tipe, ['url'], true)),
                'nullable',
                'url',
                'max:2048',
            ],
            'berkas_file' => [
                Rule::requiredIf(fn () => in_array($tipe, ['berkas', 'penugasan', 'h5p'], true) && ! $hasExistingBerkas),
                'nullable',
                'file',
                'mimes:'.$fileMimes,
                'max:'.$fileMaxKb,
            ],
            'video_file' => [
                Rule::requiredIf(fn () => $tipe === 'video' && ! $hasExistingVideo),
                'nullable',
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska,video/x-m4v',
                'mimes:'.$videoMimes,
                'max:'.$videoMaxKb,
            ],
            'survey_id' => [
                Rule::requiredIf(fn () => $tipe === 'survey'),
                'nullable',
                'exists:surveys,id',
            ],
            'is_preview' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'judul' => __('Nama'),
            'tipe' => __('Tipe aktivitas'),
            'file_url' => __('Tautan URL'),
            'berkas_file' => __('Berkas'),
            'video_file' => __('Video'),
            'body' => __('Deskripsi'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $berkasMaxMb = round(((int) config('mooc.berkas_max_kb', 10240)) / 1024, 1);
        $penugasanMaxMb = round(((int) config('mooc.penugasan_max_kb', 10240)) / 1024, 1);
        $videoMaxMb = round(((int) config('mooc.video_max_kb', 51200)) / 1024, 1);

        return [
            'file_url.required' => __('Silakan isi tautan URL.'),
            'file_url.url' => __('Format tautan tidak valid. Gunakan http:// atau https://.'),
            'berkas_file.required' => __('Silakan unggah berkas.'),
            'berkas_file.max' => __('Ukuran berkas maksimal :max MB.', [
                'max' => match(ActivityTypes::normalize((string) $this->input('tipe'))) {
                    'penugasan' => $penugasanMaxMb,
                    'h5p' => 50,
                    default => $berkasMaxMb,
                },
            ]),
            'berkas_file.mimes' => __('Format berkas tidak didukung.'),
            'video_file.required' => __('Silakan unggah video.'),
            'video_file.max' => __('Ukuran video maksimal :max MB.', ['max' => $videoMaxMb]),
            'video_file.mimes' => __('Format video tidak didukung. Gunakan MP4, WebM, MOV, AVI, atau MKV.'),
            'video_file.mimetypes' => __('Format video tidak didukung. Gunakan MP4, WebM, MOV, AVI, atau MKV.'),
        ];
    }
}
