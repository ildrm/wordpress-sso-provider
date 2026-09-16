<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\Scim;

final readonly class ParsedFilter
{
    public function __construct(
        public string $attribute,
        public string $operator,
        public string $value,
    ) {
    }
}
