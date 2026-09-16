<?php

declare(strict_types=1);

namespace WpSsoProvider\Security;

use InvalidArgumentException;

final class TokenGenerator
{
    public function opaque(int $bytes = 32): string
    {
        if ($bytes < 16 || $bytes > 128) {
            throw new InvalidArgumentException('Token entropy must be between 128 and 1024 bits.');
        }

        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }
}
