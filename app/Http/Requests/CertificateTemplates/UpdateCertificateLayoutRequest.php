<?php

namespace App\Http\Requests\CertificateTemplates;

use App\Support\CertificateVariables;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array
    {
        $validKeys = CertificateVariables::keys();
        $validKeysPattern = implode(',', $validKeys);

        return [
            'variable_positions' => ['required', 'array', 'min:1'],
            'variable_positions.*.key' => ['required', 'string', 'in:'.$validKeysPattern],
            'variable_positions.*.x' => ['required', 'numeric', 'between:0,100'],
            'variable_positions.*.y' => ['required', 'numeric', 'between:0,100'],
            'variable_positions.*.fontSize' => ['nullable', 'integer', 'between:6,72'],
            'variable_positions.*.width' => ['nullable', 'numeric', 'between:2,80'],
            'variable_positions.*.fontWeight' => ['nullable', 'string', 'in:400,500,600,700'],
            'variable_positions.*.fontStyle' => ['nullable', 'string', 'in:normal,italic'],
            'variable_positions.*.textAlign' => ['nullable', 'string', 'in:left,center,right'],
            'variable_positions.*.color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'variable_positions.required' => 'Layout variabel wajib diisi.',
            'variable_positions.min' => 'Minimal satu variabel harus ditempatkan di kanvas.',
            'variable_positions.*.key.in' => 'Variabel :input tidak dikenal.',
            'variable_positions.*.x.between' => 'Koordinat X harus antara 0 dan 100.',
            'variable_positions.*.y.between' => 'Koordinate Y harus antara 0 dan 100.',
            'variable_positions.*.fontSize.between' => 'Ukuran font harus antara 6 dan 72 pt.',
            'variable_positions.*.color.regex' => 'Format warna harus hex 6 digit (contoh: #0F2A4A).',
        ];
    }
}
