<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WpSsoProvider\Security\CryptoBox;
use WpSsoProvider\Security\SecretHasher;
use WpSsoProvider\Security\TokenGenerator;

final class SecurityPrimitivesTest extends TestCase
{
    public function test_tokens_are_random_and_stored_by_hash(): void
    {
        $generator = new TokenGenerator();
        $first = $generator->opaque(32);
        $second = $generator->opaque(32);

        self::assertNotSame($first, $second);
        self::assertGreaterThanOrEqual(43, strlen($first));

        $hasher = new SecretHasher(str_repeat('p', 32));
        $digest = $hasher->digest($first, 'authorization_code');
        self::assertNotSame($first, $digest);
        self::assertTrue($hasher->verify($first, $digest, 'authorization_code'));
        self::assertFalse($hasher->verify($second, $digest, 'authorization_code'));
    }

    public function test_authenticated_encryption_binds_context_and_detects_tampering(): void
    {
        $box = new CryptoBox(str_repeat('k', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES));
        $ciphertext = $box->encrypt('private-key', 'key:key_01');

        self::assertSame('private-key', $box->decrypt($ciphertext, 'key:key_01'));

        $this->expectException(RuntimeException::class);
        $box->decrypt($ciphertext, 'key:key_02');
    }
}
