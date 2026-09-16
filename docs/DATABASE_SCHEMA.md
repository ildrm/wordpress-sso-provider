# Database schema

Activation creates twelve tables using the current WordPress prefix:

| Table suffix | Purpose |
|---|---|
| `sso_applications` | Client/application configuration and lifecycle |
| `sso_sessions` | Dedicated SSO sessions and revocation |
| `sso_auth_codes` | Hashed, expiring, one-time OAuth codes |
| `sso_refresh_tokens` | Rotation families and reuse state |
| `sso_access_tokens` | Opaque token verifiers and revocation |
| `sso_consents` | User grants and revocation |
| `sso_keys` | Encrypted private/public key lifecycle |
| `sso_groups` | Protocol-neutral groups/rules |
| `sso_group_members` | Explicit/mapped membership |
| `sso_cas_tickets` | Hashed, service-bound CAS tickets |
| `sso_replay` | Generic replay/challenge records |
| `sso_audit_events` | Structured, reduced, append-oriented activity |

Schema version `1` is idempotently applied with `dbDelta`. Security credentials
use hash primary keys and expiry/consumption indexes. The schema contains no
foreign keys to preserve compatibility with common WordPress database operations;
application services enforce aggregate relationships.

