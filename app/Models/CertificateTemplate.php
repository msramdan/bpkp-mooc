<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\CertificateVariables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use HasUuid;

    protected $fillable = [
        'title',
        'background_image_url',
        'signature_image_url',
        'signer_name',
        'signer_title',
        'is_default',
        'variable_positions',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'variable_positions' => 'array',
        ];
    }

    /**
     * Return the effective variable layout for rendering.
     * Falls back to default layout when column is null, empty,
     * or contains corrupt JSON (caught by cast returning null).
     */
    public function layout(): array
    {
        $positions = $this->variable_positions;

        if (! is_array($positions) || empty($positions)) {
            return CertificateVariables::defaultLayout();
        }

        // Filter out unknown keys that may have sneaked in before validation was added
        $validKeys = CertificateVariables::keys();
        $positions = array_intersect_key($positions, array_flip($validKeys));

        return ! empty($positions) ? $positions : CertificateVariables::defaultLayout();
    }

    /**
     * Resolve background image URL for use in views.
     * Handles legacy text paths, uploaded storage paths, and the fallback default.
     */
    public function backgroundUrl(): string
    {
        $url = trim((string) ($this->background_image_url ?? ''));

        if ($url === '') {
            return asset('backend/assets/images/certificate-default-bg.png');
        }

        // Uploaded file (relative path from Storage::disk('public'))
        if (! str_contains($url, '://') && ! str_starts_with($url, '/') && ! str_starts_with($url, 'backend/')) {
            return asset('storage/'.$url);
        }

        // Legacy text path or absolute URL
        if (str_contains($url, '://') || str_starts_with($url, 'backend/')) {
            return $url;
        }

        return asset($url);
    }

    /**
     * Resolve signature image URL for use in views and layout editor.
     */
    public function signatureUrl(): ?string
    {
        $url = trim((string) ($this->signature_image_url ?? ''));

        if ($url === '') {
            return null;
        }

        if (! str_contains($url, '://') && ! str_starts_with($url, '/') && ! str_starts_with($url, 'backend/')) {
            return asset('storage/'.$url);
        }

        return asset($url);
    }
}
