<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\OAuth;

use InvalidArgumentException;

final class PkceVerifier
{
    public function verify(string $verifier, string $expectedChallenge, string $method): bool
    {
        if ($method !== 'S256') {
            throw new InvalidArgumentException('Only the S256 PKCE method is supported.');
        }

        if (strlen($verifier) < 43 || strlen($verifier) > 128 || preg_match('/^[A-Za-z0-9._~-]+$/D', $verifier) !== 1) {
            throw new InvalidArgumentException('Invalid PKCE code verifier.');
        }

        if (preg_match('/^[A-Za-z0-9_-]{43}$/D', $expectedChallenge) !== 1) {
            throw new InvalidArgumentException('Invalid PKCE code challenge.');
        }

        $actual = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return hash_equals($expectedChallenge, $actual);
    }
}
