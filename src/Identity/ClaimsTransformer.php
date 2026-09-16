<?php

declare(strict_types=1);

namespace WpSsoProvider\Identity;

use InvalidArgumentException;

final class ClaimsTransformer
{
    /**
     * @param mixed $value
     * @param list<array<string, mixed>> $operations
     */
    public function apply(mixed $value, array $operations): mixed
    {
        foreach ($operations as $operation) {
            $name = $operation['op'] ?? null;
            $value = match ($name) {
                'trim' => trim($this->asString($value)),
                'lowercase' => $this->lowercase($value),
                'uppercase' => $this->uppercase($value),
                'join' => $this->join($value, $operation['separator'] ?? ''),
                'map' => $this->map($value, $operation['values'] ?? []),
                default => throw new InvalidArgumentException('Unknown claim transformation.'),
            };
        }

        return $value;
    }

    private function asString(mixed $value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('Transformation requires a string.');
        }

        return $value;
    }

    private function lowercase(mixed $value): string
    {
        return mb_strtolower($this->asString($value), 'UTF-8');
    }

    private function uppercase(mixed $value): string
    {
        return mb_strtoupper($this->asString($value), 'UTF-8');
    }

    private function join(mixed $value, mixed $separator): string
    {
        if (! is_array($value) || ! is_string($separator)) {
            throw new InvalidArgumentException('Join requires an array and string separator.');
        }

        $strings = array_map(static function (mixed $part): string {
            if (! is_string($part)) {
                throw new InvalidArgumentException('Join values must be strings.');
            }
            return $part;
        }, $value);

        return implode($separator, $strings);
    }

    /** @param mixed $mappings */
    private function map(mixed $value, mixed $mappings): mixed
    {
        if (! is_string($value) || ! is_array($mappings)) {
            throw new InvalidArgumentException('Map requires a string and mapping object.');
        }

        return array_key_exists($value, $mappings) ? $mappings[$value] : $value;
    }
}
