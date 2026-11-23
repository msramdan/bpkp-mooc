<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    /** Input tanggal dari admin selalu WIB. */
    public const TZ = 'Asia/Jakarta';

    public function authorize(): bool
    {
        return $this->user()?->can('announcement manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_attachment' => $this->boolean('remove_attachment'),
            'title' => is_string($this->input('title')) ? trim($this->input('title')) : $this->input('title'),
            'link_url' => is_string($this->input('link_url')) ? trim($this->input('link_url')) : $this->input('link_url'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', Rule::in(array_keys(Announcement::CATEGORIES))],
            'audience' => ['required', Rule::in(array_keys(Announcement::AUDIENCES))],
            'display_mode' => ['required', Rule::in(array_keys(Announcement::DISPLAY_MODES))],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date_format:Y-m-d H:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d H:i'],
            // Hanya http/https: mencegah javascript:, data:, dll.
            'link_url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'link_label' => ['nullable', 'string', 'max:60'],
            'attachment' => ['nullable', 'file', 'mimes:'.implode(',', Announcement::ATTACHMENT_MIMES), 'max:'.Announcement::ATTACHMENT_MAX_KB],
            'remove_attachment' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $start = $this->wib('starts_at');
            $end = $this->wib('ends_at');
            if ($start && $end && $end->lte($start)) {
                $validator->errors()->add('ends_at', __('Tanggal berakhir harus setelah tanggal mulai.'));
            }
            if (! $this->route('announcement') && $end && $end->isPast()) {
                $validator->errors()->add('ends_at', __('Tanggal berakhir sudah lewat.'));
            }
        });
    }

    /** Waktu input (WIB) dikonversi ke zona aplikasi untuk disimpan. */
    public function wib(string $key): ?Carbon
    {
        $value = $this->input($key);
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i', $value, self::TZ)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => __('Judul'),
            'body' => __('Isi pengumuman'),
            'category' => __('Kategori'),
            'audience' => __('Ditujukan untuk'),
            'display_mode' => __('Frekuensi tampil'),
            'starts_at' => __('Mulai tayang'),
            'ends_at' => __('Berakhir'),
            'link_url' => __('Tautan'),
            'link_label' => __('Teks tombol tautan'),
            'attachment' => __('Lampiran'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'link_url.url' => __('Tautan harus diawali http:// atau https://.'),
            'attachment.mimes' => __('Format lampiran tidak didukung. Gunakan: :v.', ['v' => implode(', ', Announcement::ATTACHMENT_MIMES)]),
            'attachment.max' => __('Ukuran lampiran maksimal :mb MB.', ['mb' => Announcement::ATTACHMENT_MAX_KB / 1024]),
            'attachment.uploaded' => __('Lampiran gagal diunggah. Ukurannya kemungkinan melebihi batas upload server (:max).', ['max' => ini_get('upload_max_filesize')]),
            'starts_at.date_format' => __('Format tanggal mulai tidak valid.'),
            'ends_at.date_format' => __('Format tanggal berakhir tidak valid.'),
        ];
    }
}
