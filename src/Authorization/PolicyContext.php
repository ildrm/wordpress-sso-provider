<?php

declare(strict_types=1);

namespace WpSsoProvider\Authorization;

final readonly class PolicyContext
{
    /**
     * @param list<string> $roles
     * @param list<string> $groups
     * @param list<string> $scopes
     * @param list<string> $methods
     */
    public function __construct(
        public array $roles,
        public array $groups,
        public array $scopes,
        public array $methods,
        public int $authenticationStrength,
    ) {
    }
}
