<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use WpSsoProvider\Authentication\SsoSession;

final class SessionTest extends TestCase
{
    public function test_idle_and_absolute_expiry_and_revocation(): void
    {
        $created = new DateTimeImmutable('2026-09-16T10:00:00Z');
        $session = SsoSession::start('ses_01', 42, $created, ['pwd'], 1, 900, 3600);

        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T09:59:59Z')));
        self::assertTrue($session->isValidAt(new DateTimeImmutable('2026-09-16T10:14:59Z')));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T10:15:00Z')));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T10:15:01Z')));
        $session->touch(new DateTimeImmutable('2026-09-16T10:10:00Z'));
        self::assertTrue($session->isValidAt(new DateTimeImmutable('2026-09-16T10:24:59Z')));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T10:25:00Z')));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T11:00:00Z')));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T11:00:01Z')));
        $session->revoke(new DateTimeImmutable('2026-09-16T10:20:00Z'));
        self::assertFalse($session->isValidAt(new DateTimeImmutable('2026-09-16T10:20:01Z')));
    }

    public function test_step_up_updates_methods_and_strength_without_auth_time_loss(): void
    {
        $created = new DateTimeImmutable('2026-09-16T10:00:00Z');
        $session = SsoSession::start('ses_01', 42, $created, ['pwd'], 1, 900, 3600);
        $session->stepUp('webauthn', 3, new DateTimeImmutable('2026-09-16T10:05:00Z'));

        self::assertSame(['pwd', 'webauthn'], $session->methods());
        self::assertSame(3, $session->strength());
        self::assertSame($created, $session->authenticationTime());
    }
}
