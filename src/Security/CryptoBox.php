<?php

declare(strict_types=1);

namespace WpSsoProvider\Security;

use InvalidArgumentException;
use RuntimeException;
use SodiumException;

final class CryptoBox
{
    public function __construct(private readonly string $key)
    {
        if (strlen($key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            throw new InvalidArgumentException('Encryption key must be 256 bits.');
        }
    }

    public function encrypt(string $plaintext, string $context): string
    {
        if ($context === '') {
            throw new InvalidArgumentException('Encryption context is required.');
        }

        try {
            $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
            $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, $context, $nonce, $this->key);
        } catch (SodiumException $exception) {
            throw new RuntimeException('Encryption failed.', 0, $exception);
        }

        return 'v1.' . rtrim(strtr(base64_encode($nonce . $ciphertext), '+/', '-_'), '=');
    }

    public function decrypt(string $envelope, string $context): string
    {
        if (! str_starts_with($envelope, 'v1.')) {
            throw new RuntimeException('Unsupported encrypted envelope.');
        }

        $encoded = substr($envelope, 3);
        $padding = (4 - strlen($encoded) % 4) % 4;
        $decoded = base64_decode(strtr($encoded . str_repeat('=', $padding), '-_', '+/'), true);
        if ($decoded === false || strlen($decoded) <= SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES) {
            throw new RuntimeException('Invalid encrypted envelope.');
        }

        $nonceLength = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        $nonce = substr($decoded, 0, $nonceLength);
        $ciphertext = substr($decoded, $nonceLength);

        try {
            $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($ciphertext, $context, $nonce, $this->key);
        } catch (SodiumException $exception) {
            throw new RuntimeException('Decryption failed.', 0, $exception);
        }

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plaintext;
    }
}
