<?php

declare(strict_types=1);

namespace WpSsoProvider\Security;

use InvalidArgumentException;

final class SecretHasher
{
    public function __construct(private readonly string $pepper)
    {
        if (strlen($pepper) < 32) {
            throw new InvalidArgumentException('Secret hashing key must be at least 256 bits.');
        }
    }

    public function digest(string $secret, string $purpose): string
    {
        if ($secret === '' || $purpose === '') {
            throw new InvalidArgumentException('Secret and purpose are required.');
        }

        return hash_hmac('sha256', $purpose . "\0" . $secret, $this->pepper);
    }

    public function verify(string $secret, string $expectedDigest, string $purpose): bool
    {
        return hash_equals($expectedDigest, $this->digest($secret, $purpose));
    }
}
