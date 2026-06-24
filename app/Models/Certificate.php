<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'course_id',
        'nomor',
        'nilai_akhir',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'nilai_akhir' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isIssued(): bool
    {
        return $this->issued_at !== null;
    }
}
