<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress;

final class HealthChecks
{
    /**
     * @param array<string, mixed> $tests
     * @return array<string, mixed>
     */
    public static function register(array $tests): array
    {
        if (! isset($tests['direct']) || ! is_array($tests['direct'])) {
            $tests['direct'] = [];
        }
        $tests['direct']['wp_sso_provider_crypto'] = [
            'label' => __('SSO cryptographic readiness', 'wordpress-sso-provider'),
            'test' => [self::class, 'crypto'],
        ];

        return $tests;
    }

    /** @return array<string, mixed> */
    public static function crypto(): array
    {
        $extensionsReady = extension_loaded('sodium') && extension_loaded('openssl');
        $masterKeyReady = defined('WP_SSO_MASTER_KEY') && is_string(WP_SSO_MASTER_KEY)
            && strlen(WP_SSO_MASTER_KEY) === 32;
        $ready = $extensionsReady && $masterKeyReady;

        return [
            'label' => $ready
                ? __('SSO cryptographic prerequisites are available', 'wordpress-sso-provider')
                : __('SSO cryptographic prerequisites need attention', 'wordpress-sso-provider'),
            'status' => $ready ? 'good' : 'critical',
            'badge' => ['label' => __('Security', 'wordpress-sso-provider'), 'color' => 'blue'],
            'description' => sprintf(
                '<p>%s</p>',
                esc_html($ready
                    ? __('The required cryptographic extensions and dedicated master key are configured.', 'wordpress-sso-provider')
                    : __('Enable sodium and OpenSSL, then define WP_SSO_MASTER_KEY as a dedicated secret of exactly 32 random bytes outside the database.', 'wordpress-sso-provider'))
            ),
            'test' => 'wp_sso_provider_crypto',
        ];
    }
}
