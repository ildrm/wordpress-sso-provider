# WordPress SSO Provider

WordPress SSO Provider is an application-centric IAM foundation for WordPress.
It provides a modular core for applications, canonical identities, policy,
claims, SSO sessions, one-time credentials, key protection, audit safety, and
protocol adapters.

> **Current status: pre-release foundation (0.1.0).** Public OAuth/OIDC, SAML,
> SCIM, and CAS endpoints are deliberately not exposed yet. The implemented
> protocol primitives have tests, but no protocol is represented as production
> supported without independent interoperability and conformance evidence.

## Implemented in 0.1.0

- WordPress activation/deactivation and idempotent versioned migration.
- Twelve site-scoped IAM tables with indexes for transactional credentials.
- Custom administrative capabilities and capability-protected REST management.
- Application creation with type-aware HTTPS and native redirect validation,
  allowlisted scopes, draft lifecycle, and one-time client-secret display.
- One-way secret storage and atomic authorization-code/CAS-ticket consumption.
- Shared domain services for application lifecycle, policy decisions, claims
  transformations, session expiry/step-up, PKCE, scopes, and metadata.
- XChaCha20-Poly1305 authenticated encryption and domain-separated secret hashes.
- Recursive audit-context redaction and log-injection normalization.
- Application-centric WordPress navigation, setup readiness, Site Health check,
  responsive layout, translation calls, RTL-safe logical CSS, and visible focus.
- Reproducible Docker activation, REST, persistence, and concurrency harness.

See [Protocol support](docs/PROTOCOL_SUPPORT.md) for exact status and evidence.

## Requirements

- WordPress 6.6 or newer (verified on 7.1.0)
- PHP 8.2 or newer (verified on 8.3 and 8.5)
- MySQL 8 or MariaDB 10.6 or newer (verified on MariaDB 11.4)
- `ext-json`, `ext-openssl`, and `ext-sodium`
- A dedicated `WP_SSO_MASTER_KEY` environment/config secret of exactly 32
  random bytes before any key-bearing protocol can be enabled

## Development verification

```sh
composer install
composer test
composer analyse -- --debug
composer lint
docker compose -f test-harness/docker-compose.yml up -d
docker compose -f test-harness/docker-compose.yml exec -T cli sh /var/www/html/wp-content/plugins/wordpress-sso-provider/test-harness/smoke.sh
sh test-harness/concurrency.sh
```

## Security

Do not deploy this pre-release as an identity provider. Report vulnerabilities
privately to the project maintainers; do not include usable credentials, tokens,
or private keys. See [Security guide](docs/SECURITY.md) and the
[threat model](docs/THREAT_MODEL.md).

## License

GPL-2.0-or-later. Runtime libraries retain their own compatible licenses.
