<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\Scim;

use InvalidArgumentException;

final class FilterParser
{
    /** @var list<string> */
    private const ATTRIBUTES = ['userName', 'externalId', 'active', 'displayName', 'emails.value', 'meta.lastModified'];

    public function parse(string $filter): ParsedFilter
    {
        if (strlen($filter) > 512) {
            throw new InvalidArgumentException('SCIM filter is too long.');
        }

        $pattern = '/^([A-Za-z][A-Za-z0-9._-]*)\s+(eq|ne|co|sw|ew)\s+"([^"]*)"$/D';
        if (preg_match($pattern, $filter, $matches) !== 1 || ! in_array($matches[1], self::ATTRIBUTES, true)) {
            throw new InvalidArgumentException('Unsupported SCIM filter.');
        }

        $value = $matches[3];
        if (preg_match('//u', $value) !== 1) {
            throw new InvalidArgumentException('SCIM filter must be valid UTF-8.');
        }

        return new ParsedFilter($matches[1], $matches[2], $value);
    }
}
