<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WpSsoProvider\Authorization\PolicyContext;
use WpSsoProvider\Authorization\PolicyEngine;
use WpSsoProvider\Identity\ClaimsTransformer;

final class PolicyAndClaimsTest extends TestCase
{
    public function test_policy_is_deny_by_default_and_can_require_step_up(): void
    {
        $engine = new PolicyEngine();
        $context = new PolicyContext(['editor'], ['finance'], ['openid', 'payments'], ['pwd'], 1);

        self::assertSame('deny', $engine->evaluate([], $context)->effect);
        $decision = $engine->evaluate(
            [
                'effect' => 'allow',
                'all' => [
                    ['field' => 'role', 'operator' => 'contains', 'value' => 'editor'],
                    ['field' => 'scope', 'operator' => 'contains', 'value' => 'payments'],
                    ['field' => 'auth_strength', 'operator' => 'gte', 'value' => 2],
                ],
                'step_up' => ['webauthn'],
            ],
            $context
        );

        self::assertSame('step_up', $decision->effect);
        self::assertSame(['webauthn'], $decision->requiredMethods);
    }

    public function test_claim_transformations_are_closed_and_deterministic(): void
    {
        $transformer = new ClaimsTransformer();
        self::assertSame('ada@example.com', $transformer->apply('  ADA@EXAMPLE.COM ', [
            ['op' => 'trim'],
            ['op' => 'lowercase'],
        ]));
        self::assertSame('Ada Lovelace', $transformer->apply(['Ada', 'Lovelace'], [
            ['op' => 'join', 'separator' => ' '],
        ]));

        $this->expectException(InvalidArgumentException::class);
        $transformer->apply('input', [['op' => 'php', 'code' => 'system("id");']]);
    }
}
