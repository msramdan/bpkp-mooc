<?php

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use Concerns\RecordsActivity, HasUuids;

    public const CATEGORIES = [
        'info' => 'Info',
        'penting' => 'Penting',
        'mendesak' => 'Mendesak',
    ];

    public const AUDIENCES = [
        'peserta' => 'Peserta',
        'all' => 'Semua pengguna',
        'admin' => 'Admin saja',
    ];

    public const DISPLAY_MODES = [
        'once' => 'Sekali per pengguna (sampai ditutup)',
        'every_login' => 'Setiap kali login',
    ];

    /** Lampiran yang boleh diunggah. SVG/HTML sengaja tidak diizinkan (risiko XSS). */
    public const ATTACHMENT_MIMES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip'];

    public const ATTACHMENT_MAX_KB = 10240;

    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    protected $fillable = [
        'title', 'body', 'category', 'audience', 'display_mode', 'is_active',
        'starts_at', 'ends_at', 'link_url', 'link_label',
        'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'attachment_size' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /** Aktif dan sedang dalam rentang tayang. */
    public function scopePublished(Builder $query, ?Carbon $now = null): Builder
    {
        $now ??= now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now));
    }

    /** Pengumuman yang ditujukan ke user ini (berdasarkan role). */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        $audiences = ['all'];
        if ($user->hasRole(Roles::PESERTA)) {
            $audiences[] = 'peserta';
        }
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            $audiences[] = 'admin';
        }

        return $query->whereIn('audience', $audiences);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("CASE category WHEN 'mendesak' THEN 0 WHEN 'penting' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(starts_at, created_at) DESC');
    }

    /** published | scheduled | expired | inactive */
    public function status(?Carbon $now = null): string
    {
        $now ??= now();

        return match (true) {
            ! $this->is_active => 'inactive',
            $this->starts_at && $this->starts_at->gt($now) => 'scheduled',
            $this->ends_at && $this->ends_at->lte($now) => 'expired',
            default => 'published',
        };
    }

    public function isVisibleTo(User $user): bool
    {
        return static::query()->whereKey($this->getKey())->published()->forUser($user)->exists();
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_path);
    }

    public function attachmentIsImage(): bool
    {
        return $this->hasAttachment() && in_array($this->attachment_mime, self::IMAGE_MIMES, true);
    }

    public function attachmentSizeLabel(): string
    {
        $kb = (int) ceil(((int) $this->attachment_size) / 1024);

        return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.').' MB' : $kb.' KB';
    }

    public function attachmentExtension(): string
    {
        return strtoupper(pathinfo((string) $this->attachment_name, PATHINFO_EXTENSION) ?: 'FILE');
    }

    protected function activityLogExcept(): array
    {
        return ['attachment_path'];
    }
}
