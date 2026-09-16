<?php

declare(strict_types=1);

namespace WpSsoProvider\Authorization;

use InvalidArgumentException;

final class PolicyEngine
{
    /** @param array<string, mixed> $policy */
    public function evaluate(array $policy, PolicyContext $context): PolicyDecision
    {
        if ($policy === []) {
            return new PolicyDecision('deny', ['No access policy matched.']);
        }

        if (($policy['effect'] ?? 'deny') !== 'allow') {
            return new PolicyDecision('deny', ['Policy effect is deny.']);
        }

        $conditions = $policy['all'] ?? [];
        if (! is_array($conditions)) {
            throw new InvalidArgumentException('Policy conditions must be an array.');
        }

        $failed = [];
        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                $failed[] = 'invalid_condition';
                continue;
            }

            $normalized = $this->normalizeCondition($condition);
            if (! $this->conditionMatches($normalized, $context)) {
                $failed[] = is_string($condition['field'] ?? null)
                    ? $condition['field']
                    : 'invalid_condition';
            }
        }

        if ($failed === []) {
            return new PolicyDecision('allow');
        }

        $stepUp = $policy['step_up'] ?? [];
        if (in_array('auth_strength', $failed, true) && is_array($stepUp) && $stepUp !== []) {
            $methods = array_values(array_filter($stepUp, 'is_string'));
            return new PolicyDecision('step_up', ['Stronger authentication is required.'], $methods);
        }

        return new PolicyDecision('deny', ['Policy conditions did not match.']);
    }

    /** @param array<string, mixed> $condition */
    private function conditionMatches(array $condition, PolicyContext $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        return match ([$field, $operator]) {
            ['role', 'contains'] => is_string($value) && in_array($value, $context->roles, true),
            ['group', 'contains'] => is_string($value) && in_array($value, $context->groups, true),
            ['scope', 'contains'] => is_string($value) && in_array($value, $context->scopes, true),
            ['method', 'contains'] => is_string($value) && in_array($value, $context->methods, true),
            ['auth_strength', 'gte'] => is_int($value) && $context->authenticationStrength >= $value,
            default => throw new InvalidArgumentException('Unknown policy field or operator.'),
        };
    }

    /**
     * @param array<mixed, mixed> $condition
     * @return array<string, mixed>
     */
    private function normalizeCondition(array $condition): array
    {
        $normalized = [];
        foreach ($condition as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Policy condition keys must be strings.');
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
