<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Primary key UUID (v4). Tabel users tetap bigint.
 */
trait HasUuid
{
    use HasUuids;
}
