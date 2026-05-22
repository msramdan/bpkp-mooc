<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('course create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:20', 'unique:courses,kode'],
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:120'],
            'instruktur' => ['required', 'string', 'max:255'],
            'thumbnail' => ['required', 'url', 'max:500'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:500'],
            'modul_total' => ['required', 'integer', 'min:1', 'max:30'],
            'level' => ['required', 'string', 'max:30', Rule::in(['Pemula', 'Menengah', 'Lanjutan'])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'deskripsi' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
