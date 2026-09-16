<?php

declare(strict_types=1);

namespace WpSsoProvider\Protocol\Oidc;

use InvalidArgumentException;

final class DiscoveryMetadata
{
    public function __construct(private readonly string $issuer)
    {
        if (! str_starts_with($issuer, 'https://') || str_ends_with($issuer, '/')) {
            throw new InvalidArgumentException('Issuer must be an HTTPS URL without a trailing slash.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'issuer' => $this->issuer,
            'authorization_endpoint' => $this->issuer . '/authorize',
            'token_endpoint' => $this->issuer . '/token',
            'userinfo_endpoint' => $this->issuer . '/userinfo',
            'jwks_uri' => $this->issuer . '/jwks',
            'revocation_endpoint' => $this->issuer . '/revoke',
            'introspection_endpoint' => $this->issuer . '/introspect',
            'end_session_endpoint' => $this->issuer . '/logout',
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token', 'client_credentials'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'private_key_jwt', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => ['openid', 'profile', 'email', 'roles', 'groups'],
            'claims_supported' => ['sub', 'iss', 'aud', 'exp', 'iat', 'auth_time', 'nonce', 'acr', 'amr', 'name', 'given_name', 'family_name', 'nickname', 'picture', 'email', 'email_verified', 'locale', 'zoneinfo', 'roles', 'groups'],
        ];
    }
}
