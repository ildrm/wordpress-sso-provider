<?php

declare(strict_types=1);

namespace WpSsoProvider\Authorization;

final readonly class PolicyDecision
{
    /**
     * @param list<string> $reasons
     * @param list<string> $requiredMethods
     */
    public function __construct(
        public string $effect,
        public array $reasons = [],
        public array $requiredMethods = [],
    ) {
    }
}
