# ADR 0002: Protocol and cryptographic security baseline

Status: Accepted — 2026-09-16

## Sources

- OAuth 2.0 RFC 6749 plus Security BCP RFC 9700; PKCE RFC 7636; metadata RFC 8414;
  revocation RFC 7009; introspection RFC 7662; JWT profile RFC 9068 where used.
- OpenID Connect Core and Discovery 1.0 incorporating errata set 2; RP-Initiated
  Logout 1.0.
- OASIS SAML 2.0 Core, Bindings, Profiles, Metadata, and errata 05.
- SCIM Core RFC 7643 and Protocol RFC 7644, including applicable updates.
- Apereo CAS Protocol 3.0.
- W3C Web Authentication Level 3 Recommendation.

## Decision

Authorization code plus S256 PKCE is the only browser authorization flow in the
initial profile. Redirect strings match exactly. Access tokens are opaque by
default; ID tokens use asymmetric signatures and published JWKS. Refresh tokens
rotate by family and reuse revokes the family. TLS is mandatory outside explicit
development loopback handling.

SAML/XML signatures and WebAuthn primitives use maintained external libraries;
the plugin never implements those cryptographic formats itself. Private material
uses libsodium XChaCha20-Poly1305 with a site-specific key derived from a dedicated
environment secret; installations without a suitable secret fail readiness for
key-bearing protocols.

PHP 8.2 is the runtime floor because the supported WebAuthn 5.x library requires
it. Composer blocked the older PHP-8.1-compatible line due to a security advisory;
that advisory is not ignored or allowlisted.

## Rejected

Implicit/password grants, wildcard callbacks, symmetric ID-token signing,
`alg=none`, SHA-1, unsigned production SAML, reversible client-secret storage when
only verification is needed, and email-only account linking.
