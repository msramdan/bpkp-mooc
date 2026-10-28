<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tag kursus berupa nama kompetensi dari Aplikasi TNA.
 */
class CourseTag extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_id',
        'name',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
