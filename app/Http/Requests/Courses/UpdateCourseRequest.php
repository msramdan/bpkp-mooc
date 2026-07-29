<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('course edit');
    }

    protected function prepareForValidation(): void
    {
        if ($this->id_number === '') {
            $this->merge(['id_number' => null]);
        }

        $this->merge([
            'ends_at_enabled' => $this->boolean('ends_at_enabled'),
            'is_published' => $this->boolean('is_published'),
            'remove_thumbnail' => $this->boolean('remove_thumbnail'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'instansi' => ['required', 'in:Internal,Eksternal'],
            'kategori' => ['required', 'string', 'max:120', 'exists:learning_categories,name'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:learning_tags,id'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_thumbnail' => ['sometimes', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'ends_at_enabled' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'judul' => __('Nama kursus'),
            'instansi' => __('Instansi'),
            'kategori' => __('Kategori'),
            'tag_ids' => __('Tag'),
            'tag_ids.*' => __('Tag'),
            'id_number' => __('Nomor ID kursus'),
            'starts_at' => __('Tanggal mulai'),
            'ends_at' => __('Tanggal selesai'),
            'deskripsi' => __('Ringkasan kursus'),
            'thumbnail_file' => __('Thumbnail'),
        ];
    }
}
