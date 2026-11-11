<?php

namespace App\Libraries;

final class SetupKey
{
    public static function valid(string $configured, string $provided): bool
    {
        return preg_match('/\A[0-9a-f]{64}\z/', $configured) === 1
            && hash_equals($configured, $provided);
    }
}
