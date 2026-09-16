# Threat model

Version 0.1 — STRIDE review, 2026-09-16

## Scope and trust boundaries

Assets: user credentials, SSO sessions, authorization grants, signing/encryption
keys, client credentials, identity data, policy, audit evidence. Boundaries exist
between user agent/WordPress, client/protocol endpoint, WordPress/database/cache,
administrator/management API, upstream source/adapter, and outbound URL fetches.

| ID | STRIDE | Threat / entry point | Mitigation and verification | Residual risk |
|---|---|---|---|---|
| TM-1 | S/E | Login or REST authorization bypass | WP auth + capability callbacks; centralized policy; negative permission tests | Compromised admin retains intended authority |
| TM-2 | T/E | Code/ticket/refresh replay race | Hash at rest; indexed conditional consume in transaction; concurrency test | DB isolation/configuration errors |
| TM-3 | I | Token/key/secret leakage via DB/log/UI | Opaque hashes; AEAD private keys; redacting logger; response tests + secret scan | Runtime memory compromise |
| TM-4 | S/T | OAuth mix-up, code interception, open redirect | Exact redirect, issuer, client/redirect/code binding, S256 PKCE, no open redirects | Compromised client endpoint |
| TM-5 | T/E | JWT algorithm confusion or malicious `kid` | Fixed per-app allowlist, asymmetric algorithms, local key lookup, malformed vectors | Crypto-library defect |
| TM-6 | T/E | SAML signature wrapping/XXE/replay | Audited library, hardened parser, signed-element reference validation, replay store, hostile fixtures | Dependency zero-day |
| TM-7 | S/T | WebAuthn challenge/origin/RP mismatch | Audited library; stored expiring challenges; exact origin/RP; sign-count policy | Cloned authenticator limitations |
| TM-8 | I/E | SSRF/DNS rebinding in metadata/webhooks | HTTPS, no userinfo, resolve/block reserved ranges, recheck redirects, bounded body/time | TOCTOU at hostile DNS/provider edge |
| TM-9 | E | SCIM object-level authorization failure | Per-client tenant/site/scope filters in repositories; IDOR tests | Misconfigured broad provisioning client |
| TM-10 | T | LDAP injection or untrusted SPNEGO header | RFC4515 escaping; TLS verification; trusted proxy allowlist + signed/authenticated boundary | Compromised trusted proxy/directory |
| TM-11 | R | Actor denies security-sensitive change | Append-only structured audit with actor/request ID and before/after digest | DB administrator can alter storage |
| TM-12 | D | Credential stuffing/flood/oversized parsing | Multi-dimensional throttles, body limits, cheap validation first, no victim-only lockout | Distributed low-rate attacks |
| TM-13 | T/I | CSRF/XSS in admin, consent, logout | Nonces/state, SameSite/Secure/HttpOnly, output escaping, CSP-compatible assets, browser tests | Vulnerable co-installed plugin |
| TM-14 | E | Unsafe policy/claim transformation | Closed operator registry; typed schema; no eval/dynamic code; fuzz tests | Extension code is privileged |
| TM-15 | I | Cross-site multisite data leak | Site/network scope in every repository key and authorization; multisite tests | Network admin can intentionally share |
| TM-16 | S/E | Session fixation/theft or revoked-session refresh | Rotate session IDs, hash identifiers, expiry, device context, commit-time session check | Stolen active browser session |
| TM-17 | T | Malicious/partial migration | Versioned idempotent migration, schema checks, backup guidance, interrupted-run tests | Infrastructure failure during DDL |
| TM-18 | D/I | Error detail/resource exhaustion | Correlation-only public errors; bounded queries/pagination/crypto; no traces/paths | Timing side channels minimized, not eliminated |

All threats score at or above high impact because the system is an identity provider.
Release gating requires named tests in the traceability matrix and zero unresolved
Critical/High findings. Threat model and secret scan are rerun after implementation.

