# Troubleshooting

## Dependencies missing

Install production Composer dependencies and verify `vendor/autoload.php` exists.

## Cryptographic readiness is critical

Enable sodium/OpenSSL and set a dedicated `WP_SSO_MASTER_KEY` of exactly 32
random bytes outside the database. Do not reuse `AUTH_KEY` or another WP salt.

## Application create returns 400

Check that type is one of the documented presets, interactive clients have an
exact absolute redirect URI, web/SPA clients use HTTPS, native/desktop clients
use a permitted HTTPS, reverse-domain private scheme, or literal loopback URI,
and scopes are from `openid profile email roles groups`.

## Tables are missing

Deactivate/reactivate in a maintenance window and inspect the WordPress debug log
for `dbDelta` errors. Verify the database user can create and alter prefixed tables.

Public protocol errors such as invalid PKCE, issuer, SAML audience, SCIM auth, and
CAS service are not applicable in 0.1.0 because those endpoints are not exposed.

