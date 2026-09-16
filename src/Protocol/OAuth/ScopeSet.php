<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\OAuth;

use InvalidArgumentException;

final class ScopeSet
{
    /** @param list<string> $scopes */
    private function __construct(private readonly array $scopes)
    {
    }

    /** @param list<string> $allowedScopes */
    public static function fromRequest(string $requested, array $allowedScopes): self
    {
        $requested = trim($requested);
        if ($requested === '') {
            return new self([]);
        }

        $scopes = preg_split('/ +/', $requested);
        if (! is_array($scopes)) {
            throw new InvalidArgumentException('Invalid scope value.');
        }

        $unique = [];
        foreach ($scopes as $scope) {
            if (preg_match('/^[\x21\x23-\x5B\x5D-\x7E]+$/D', $scope) !== 1 || ! in_array($scope, $allowedScopes, true)) {
                throw new InvalidArgumentException('Unknown or prohibited scope.');
            }
            if (! in_array($scope, $unique, true)) {
                $unique[] = $scope;
            }
        }

        return new self($unique);
    }

    /** @return list<string> */
    public function all(): array
    {
        return $this->scopes;
    }

    public function contains(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }
}
