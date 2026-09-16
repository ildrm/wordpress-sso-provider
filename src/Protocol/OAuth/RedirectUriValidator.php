<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\OAuth;

use InvalidArgumentException;

final class RedirectUriValidator
{
    /** @param list<string> $registeredUris */
    public function matches(string $requestedUri, array $registeredUris): bool
    {
        foreach ($registeredUris as $registeredUri) {
            if (hash_equals($registeredUri, $requestedUri)) {
                return true;
            }
        }

        return false;
    }

    public function assertRegistrable(string $uri, bool $allowLoopbackHttp = true): void
    {
        if ($uri === '' || preg_match('/[\x00-\x20\x7f]/', $uri) === 1 || str_contains($uri, '*')) {
            throw new InvalidArgumentException('Redirect URI contains prohibited characters.');
        }

        $parts = parse_url($uri);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('Redirect URI must be absolute.');
        }

        if (isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('Redirect URI must not contain fragments or user information.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower(rtrim($parts['host'], '.'));
        $isLoopback = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if ($scheme !== 'https' && ! ($allowLoopbackHttp && $scheme === 'http' && $isLoopback)) {
            throw new InvalidArgumentException('Redirect URI must use HTTPS.');
        }
    }
}
