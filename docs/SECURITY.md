# Security guide

## Required baseline

- HTTPS for non-loopback traffic.
- A dedicated `WP_SSO_MASTER_KEY` outside the database and separate from WP salts.
- Sodium and OpenSSL extensions.
- Exact redirect registration and S256 PKCE.
- Least-privilege assignment of `manage_sso_provider`, `manage_sso_security`, and
  `view_sso_audit`.

Retrievable private material uses XChaCha20-Poly1305 with authenticated context.
Verification-only secrets use password hashing or purpose-separated HMAC digests.
One-time credentials are stored as hashes and consumed with a conditional database
update, preventing check-then-update races.

Audit context passes through a closed redactor for passwords, secrets, bearer
headers, tokens, codes, private keys, OTPs, assertions, tickets, and challenges.
Control characters are normalized to prevent forged log lines.

No public federation/provisioning endpoint is enabled in 0.1.0. This is a security
gate, not a configuration omission. See `docs/THREAT_MODEL.md` for residual risks.

