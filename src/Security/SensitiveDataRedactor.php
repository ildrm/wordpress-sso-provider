<?php

declare(strict_types=1);

namespace WpSsoProvider\Security;

final class SensitiveDataRedactor
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'client_secret',
        'authorization',
        'access_token',
        'refresh_token',
        'id_token',
        'authorization_code',
        'code_verifier',
        'code_challenge',
        'private_key',
        'otp',
        'totp',
        'recovery_code',
        'saml_assertion',
        'cas_ticket',
        'webauthn_challenge',
    ];

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function redact(array $context): array
    {
        $safe = [];
        foreach ($context as $key => $value) {
            $normalized = strtolower(str_replace('-', '_', $key));
            if (in_array($normalized, self::SENSITIVE_KEYS, true)) {
                $safe[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $safe[$key] = $this->redactMixedArray($value);
                continue;
            }

            $safe[$key] = is_string($value) ? $this->sanitizeString($value) : $value;
        }

        return $safe;
    }

    /**
     * @param array<array-key, mixed> $values
     * @return array<array-key, mixed>
     */
    private function redactMixedArray(array $values): array
    {
        $safe = [];
        foreach ($values as $key => $value) {
            $normalized = is_string($key) ? strtolower(str_replace('-', '_', $key)) : '';
            if ($normalized !== '' && in_array($normalized, self::SENSITIVE_KEYS, true)) {
                $safe[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $safe[$key] = $this->redactMixedArray($value);
            } else {
                $safe[$key] = is_string($value) ? $this->sanitizeString($value) : $value;
            }
        }

        return $safe;
    }

    private function sanitizeString(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        return mb_substr($value, 0, 1024, 'UTF-8');
    }
}
