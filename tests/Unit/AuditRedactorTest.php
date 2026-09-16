<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WpSsoProvider\Security\SensitiveDataRedactor;

final class AuditRedactorTest extends TestCase
{
    public function testSensitiveValuesAreRecursivelyRedacted(): void
    {
        $redacted = (new SensitiveDataRedactor())->redact([
            'client_id' => 'public-id',
            'client_secret' => 'usable-secret',
            'authorization' => 'Bearer usable-token',
            'nested' => [
                'refresh_token' => 'usable-refresh-token',
                'reason' => "failure\nforged-log-line",
            ],
        ]);

        self::assertSame('public-id', $redacted['client_id']);
        self::assertSame('[REDACTED]', $redacted['client_secret']);
        self::assertSame('[REDACTED]', $redacted['authorization']);
        self::assertSame('[REDACTED]', $redacted['nested']['refresh_token']);
        self::assertSame('failure forged-log-line', $redacted['nested']['reason']);
    }
}
