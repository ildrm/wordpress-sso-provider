<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\OAuth;

use InvalidArgumentException;
use WpSsoProvider\Domain\Application\ApplicationType;

final class RedirectUriValidator
{
    /** @param list<string> $registeredUris */
    public function matches(string $requestedUri, array $registeredUris, ApplicationType $type = ApplicationType::Web): bool
    {
        try {
            $this->assertRegistrable($requestedUri, $type);
        } catch (InvalidArgumentException) {
            return false;
        }

        foreach ($registeredUris as $registeredUri) {
            try {
                $this->assertRegistrable($registeredUri, $type);
            } catch (InvalidArgumentException) {
                continue;
            }

            if (hash_equals($registeredUri, $requestedUri)) {
                return true;
            }

            // RFC 8252 permits changing only the port of a literal loopback redirect.
            if (
                $this->isLoopback($registeredUri) && $this->isLoopback($requestedUri)
                && hash_equals($this->withoutLoopbackPort($registeredUri), $this->withoutLoopbackPort($requestedUri))
            ) {
                return true;
            }
        }

        return false;
    }

    public function assertRegistrable(string $uri, ApplicationType $type = ApplicationType::Web): void
    {
        if (
            $uri === '' || strlen($uri) > 2048 || preg_match('/[^\x21-\x7e]/', $uri) === 1
            || str_contains($uri, '*') || str_contains($uri, '\\')
            || preg_match('/%(?![0-9A-Fa-f]{2})/', $uri) === 1
            || preg_match('/%(?:0[0-9A-Fa-f]|1[0-9A-Fa-f]|7[Ff]|5[Cc])/', $uri) === 1
        ) {
            throw new InvalidArgumentException('Redirect URI contains prohibited characters or encoding.');
        }

        $parts = parse_url($uri);
        if (
            ! is_array($parts) || ! isset($parts['scheme'])
            || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])
        ) {
            throw new InvalidArgumentException('Redirect URI is malformed or contains a fragment or user information.');
        }

        $scheme = $parts['scheme'];
        if ($scheme === 'https') {
            $this->assertHttps($parts);
            return;
        }

        $native = in_array($type, [ApplicationType::Native, ApplicationType::Desktop], true);
        if ($native && $scheme === 'http' && $this->isLoopback($uri)) {
            $this->assertPort($parts);
            return;
        }

        if (
            $native && preg_match('/^[a-z][a-z0-9-]*(?:\.[a-z][a-z0-9-]*){2,}$/D', $scheme) === 1
            && filter_var($scheme, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            && ! isset($parts['host']) && ! isset($parts['port'])
            && isset($parts['path']) && str_starts_with($parts['path'], '/')
            && ! str_starts_with($parts['path'], '//')
        ) {
            return;
        }

        throw new InvalidArgumentException('Redirect URI is not permitted for this application type.');
    }

    /** @param array<string, int|string> $parts */
    private function assertHttps(array $parts): void
    {
        $host = $parts['host'] ?? null;
        if (
            ! is_string($host) || $host === '' || $host !== strtolower($host)
            || str_ends_with($host, '.') || ! str_contains($host, '.')
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            throw new InvalidArgumentException('HTTPS redirect URI requires a valid ASCII DNS host.');
        }

        $this->assertPort($parts);
    }

    /** @param array<string, int|string> $parts */
    private function assertPort(array $parts): void
    {
        if (isset($parts['port']) && (! is_int($parts['port']) || $parts['port'] < 1 || $parts['port'] > 65535)) {
            throw new InvalidArgumentException('Redirect URI port is invalid.');
        }
    }

    private function isLoopback(string $uri): bool
    {
        return preg_match('~^http://(?:127\.0\.0\.1|\[::1\])(?::[0-9]+)?(?:/|\?|$)~D', $uri) === 1;
    }

    private function withoutLoopbackPort(string $uri): string
    {
        return preg_replace('~^(http://(?:127\.0\.0\.1|\[::1\]))(?::[0-9]+)?(?=/|\?|$)~D', '$1', $uri, 1) ?? $uri;
    }
}
