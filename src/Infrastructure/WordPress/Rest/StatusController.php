<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress\Rest;

use WP_REST_Request;
use WP_REST_Response;

final class StatusController
{
    public function register(): void
    {
        register_rest_route(
            'wp-sso-provider/v1',
            '/status',
            [
                'methods' => 'GET',
                'callback' => [$this, 'status'],
                'permission_callback' => static fn (): bool => current_user_can('manage_sso_provider'),
            ]
        );
    }

    public function status(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);

        $schemaVersion = get_option('wp_sso_provider_schema_version', '0');

        return new WP_REST_Response(
            [
                'version' => WP_SSO_PROVIDER_VERSION,
                'schema_version' => is_scalar($schemaVersion) ? (string) $schemaVersion : '0',
                'https' => is_ssl(),
                'crypto' => [
                    'sodium' => extension_loaded('sodium'),
                    'openssl' => extension_loaded('openssl'),
                    'master_key_configured' => defined('WP_SSO_MASTER_KEY') && is_string(WP_SSO_MASTER_KEY)
                        && strlen(WP_SSO_MASTER_KEY) === 32,
                ],
                'protocols' => [
                    'oauth' => 'experimental_not_exposed',
                    'oidc' => 'experimental_not_exposed',
                    'saml' => 'experimental_not_exposed',
                    'scim' => 'experimental_not_exposed',
                    'cas' => 'experimental_not_exposed',
                    'ws_federation' => 'unsupported',
                ],
            ],
            200
        );
    }
}
