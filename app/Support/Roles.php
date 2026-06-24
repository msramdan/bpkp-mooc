<?php

namespace App\Support;

final class Roles
{
    public const SUPER_ADMIN = 'super_admin';

    public const PESERTA = 'peserta';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [self::SUPER_ADMIN, self::PESERTA];
    }
}
