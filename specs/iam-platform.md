# WordPress SSO Provider — Implementation Specification

**Author:** Engineering Team  
**Date:** 2026-09-16  
**Status:** Approved  
**Authority:** User-supplied master implementation prompt  
**Product:** WordPress SSO Provider  
**Version target:** 0.1.0  
**Reviewers:** Architecture, protocol, security, WordPress, QA, accessibility

## Context

WordPress users need to act as canonical identities for external web, native,
enterprise, API, and provisioning clients. The plugin is a modular IAM platform:
protocol adapters use shared application, identity, claims, policy, session,
consent, replay, key, and audit services. It is not a set of isolated protocol
handlers.

The first release is secure-by-default and honest about verification. A feature
is advertised only when its implementation, negative tests, and required
independent interoperability evidence exist. Experimental adapters remain off by
default and are not present in discovery metadata.

## Functional Requirements

- FR-1: The plugin MUST install, activate, migrate, deactivate, upgrade,
  and uninstall according to an explicit preservation policy without modifying
  WordPress core.
- FR-2: Protocols MUST consume common identity, application,
  claims, scope, policy, consent, session, replay, key, rate-limit, and audit
  ports. Controllers MUST remain transport-only.
- FR-3: Administrators MUST manage lifecycle-controlled
  applications (`draft`, `configured`, `tested`, `active`, `disabled`) and exact
  registered endpoints. An untested application MUST NOT become active.
- FR-4: WordPress users MUST be normalized to a canonical identity;
  adapters MUST NOT independently derive identity attributes.
- FR-5: A deterministic, deny-by-default policy engine MUST
  evaluate application, user, role, group, scope, protocol, and authentication
  context without executing administrator code.
- FR-6: A dedicated, revocable SSO session MUST record authentication
  time/method/strength and enforce idle and absolute expiry.
- FR-7: The authorization server MUST implement authorization code with
  S256 PKCE, refresh rotation, client credentials, revocation, introspection, and
  RFC 8414 metadata. Exact redirect matching is mandatory. Implicit and password
  grants MUST NOT be implemented.
- FR-8: The OP MUST implement discovery, code flow, signed ID tokens,
  UserInfo, nonce/auth_time/acr/amr, JWKS, and validated RP-initiated logout. It
  MUST advertise only tested capabilities.
- FR-9: The SAML IdP MUST use an audited library for signature/encryption,
  validate registered issuer/ACS/destination/audience/time/request/replay, publish
  metadata, and reject hostile XML. It MUST remain unavailable until the runtime
  dependency and interoperability gate pass.
- FR-10: The service provider MUST expose RFC 7643/7644 discovery, Users,
  and Groups with strongly authenticated CRUD/PATCH, filtering, pagination,
  versioning, and SCIM errors.
- FR-11: CAS MUST issue hashed, expiring, application-bound, atomically
  consumed service tickets and implement registered-service CAS 1/2/3 validation.
- FR-12: LDAP and Kerberos/SPNEGO MUST use isolated identity-source
  and trusted-upstream boundaries; unverified headers MUST NOT authenticate users.
- FR-13: Password, TOTP, recovery code, and WebAuthn methods
  MUST feed one authentication result and support policy-driven step-up.
- FR-14: A central claims engine MUST provide allowlisted source fields,
  scopes, groups, and safe transformations without `eval` or dynamic code.
- FR-15: Consent MUST show requested scopes/data, detect expansion,
  persist grants, and allow revocation.
- FR-16: Signing and encryption keys MUST have lifecycle state, `kid`,
  algorithm, overlap/rotation, and authenticated encryption at rest. Password-like
  verifier secrets MUST be one-way hashed when retrieval is unnecessary.
- FR-17: One-time credentials MUST be consumed with a single atomic
  persistence operation and tested under concurrent redemption.
- FR-18: Structured audit/security events MUST exclude usable credentials,
  secrets, OTPs, private keys, raw tokens, codes, and tickets.
- FR-19: The application-centric UI MUST include guided setup,
  applications, directory, access, security, provisioning, activity, diagnostics,
  and settings with progressive disclosure and actionable errors.
- FR-20: Capability-protected REST and WP-CLI surfaces MUST call the
  application layer and validate schemas/permissions.
- FR-21: Site Health, cron cleanup, correlation IDs, deterministic
  diagnostics, cache compatibility, multisite scoping, privacy integration, and
  signed webhooks MUST be supported where enabled.
- FR-22: Interfaces and guarded registration APIs MUST exist for
  adapters, sources, claims, authentication, policy, templates, and audit listeners.
- FR-23: A deterministic release ZIP MUST contain runtime files and
  production dependencies only and MUST pass clean install/activation smoke tests.
- FR-24: Each protocol MUST expose an honest status based on unit,
  integration, negative, independent-client, and conformance evidence.

## Non-Functional Requirements

- **NFR-1 Security:** No unresolved Critical or High finding is releasable. TLS is
  required for non-loopback protocol traffic; secure defaults cannot be bypassed
  silently.
- **NFR-2 Atomicity:** Concurrent redemption of the same code, ticket, challenge,
  or rotating refresh token MUST yield exactly one successful consumer.
- **NFR-3 Accessibility:** Administrative and user flows MUST target WCAG 2.2 AA,
  including keyboard operation, visible focus, labels, announcements, and no
  color-only status.
- **NFR-4 Internationalization:** All user-visible strings MUST be translatable;
  layouts MUST use logical properties and remain usable in RTL.
- **NFR-5 Compatibility:** Runtime target is WordPress 6.6+ and PHP 8.2–8.5 on
  MySQL 8+/MariaDB 10.6+, single site and multisite. Actual tested cells are
  documented separately.
- **NFR-6 Performance:** Baseline p95 targets under representative local load are
  token/UserInfo < 500 ms, admin reads < 750 ms, and SCIM list < 1 s for 1,000
  records; these remain `NOT RUN` until measured.
- **NFR-7 Availability:** Runtime state MUST use database/shared object cache, not
  process memory or node-local files.
- **NFR-8 Privacy:** Network metadata MUST be minimized, retention-configurable,
  and covered by exporter/eraser behavior where applicable.
- **NFR-9 Quality:** Strict types, PSR-4, WPCS, static analysis, unit/integration/
  protocol/security/E2E tests, locked dependencies, and no production debug/TODO
  markers are required for release.

## Acceptance Criteria

### AC-1: Clean activation and guarded lifecycle (FR-1, FR-3)

Given a clean supported WordPress instance, when the ZIP
  is activated, then migrations complete idempotently and only a configured and
  successfully tested application can transition to active.
### AC-2: Atomic code exchange (FR-7, NFR-2)

Given a valid code bound to client, redirect, and S256
  challenge, when two exchanges race, then exactly one returns tokens and the
  other returns `invalid_grant` without leaking detail.
### AC-3: Exact redirect enforcement (FR-7)

Given any redirect URI differing from the registered string,
  when authorization is requested, then no redirect occurs and a local actionable
  error with correlation ID is shown.
### AC-4: Verifiable OIDC code flow (FR-8)

Given an active OIDC client and authenticated authorized user,
  when code flow completes, then ID token signature, issuer, audience, times,
  nonce, and `at_hash` are independently verifiable from discovery/JWKS.
### AC-5: Validated logout (FR-6, FR-8)

Given a valid `id_token_hint` and registered post-logout
  URI, when logout is requested, then the SSO session is revoked and redirection
  is limited to that registered URI.
### AC-6: SCIM contract (FR-10)

Given a scoped SCIM bearer credential, when Users/Groups CRUD,
  PATCH, filter, pagination, and discovery requests are made, then responses use
  `application/scim+json`, correct schemas/statuses, and stable versions.
### AC-7: CAS ticket binding and atomicity (FR-11, NFR-2)

Given a CAS ticket issued for Service A, when validated
  twice or against Service B, then no more than the first correct validation
  succeeds.
### AC-8: MFA step-up (FR-13)

Given a policy requiring MFA, when password authentication is
  complete, then the transaction resumes only after a verified non-replayed TOTP,
  recovery code, or passkey result and records `amr`/strength.
### AC-9: Claims and expanded consent (FR-14, FR-15)

Given newly requested scopes, when consent is required,
  then a localized accessible screen identifies shared data and denial issues no
  credential; approval persists an auditable grant.
### AC-10: Safe key rotation (FR-16)

Given active and retiring signing keys, when rotation occurs,
  then new tokens use the new `kid`, both public keys remain published through the
  overlap window, and private material never appears in logs/API/UI.
### AC-11: Accessible responsive administration (FR-19, NFR-3, NFR-4)

Given keyboard-only use at 320 CSS pixels in
  English and RTL, when setup and application creation are completed, then all
  controls remain reachable, labeled, focused, and status is announced.
### AC-12: Release evidence (FR-23, FR-24)

Given the exact release ZIP, when installed in a clean
  HTTPS environment, then activation, OIDC smoke flow, deactivate/reactivate, and
  integrity checks pass and the evidence table records all unrun gates honestly.

### AC-13: Cross-cutting boundaries and operations (FR-2, FR-4, FR-5, FR-9, FR-12, FR-17, FR-18, FR-20, FR-21, FR-22)

Given each public adapter and management surface, when its contract and negative
tests execute, then it calls the shared identity/policy/session/replay/audit ports,
enforces the applicable capability and site scope, and unavailable upstream or
SAML dependencies fail closed without advertising support.

## Edge Cases

- EC-1: Duplicate/array/null-byte/invalid-UTF-8 parameters produce controlled
  protocol errors.
- EC-2: Expired, future, wrong-client, wrong-redirect, wrong-audience, and
  replayed credentials fail closed.
- EC-3: Clock skew is bounded and never disables expiry validation.
- EC-4: Unknown scopes, response types, algorithms, bindings, schemas, fields,
  and policy operators are rejected.
- EC-5: Oversized JSON/XML/form/header input is rejected before expensive work.
- EC-6: DNS rebinding, redirects to private/reserved addresses, credentials in
  URLs, and non-HTTPS metadata/webhook destinations fail unless an explicit local
  infrastructure policy applies.
- EC-7: Cache/database outage does not convert denial into authorization.
- EC-8: Rotation during request processing uses a stable key/secret snapshot.
- EC-9: Session revocation racing refresh deterministically denies issuance
  after revocation commits.
- EC-10: Multisite site/network identifiers are present in every scoped lookup.

## API Contracts

```ts
type Protocol = 'oauth'|'oidc'|'saml'|'scim'|'cas';
type Lifecycle = 'draft'|'configured'|'tested'|'active'|'disabled';
interface Application { id:string; name:string; type:string; status:Lifecycle;
  trusted:boolean; protocols:Protocol[]; version:number; }
interface Problem { code:string; message:string; request_id:string;
  details?:Record<string,string|string[]>; }
interface TokenResponse { token_type:'Bearer'; access_token:string;
  expires_in:number; scope:string; refresh_token?:string; id_token?:string; }
interface ScimList<T> { schemas:['urn:ietf:params:scim:api:messages:2.0:ListResponse'];
  totalResults:number; startIndex:number; itemsPerPage:number; Resources:T[]; }
interface PolicyDecision { effect:'allow'|'deny'|'step_up'; reasons:string[];
  scopes:string[]; claims:string[]; required_methods:string[]; }
```

Management routes are namespaced under `/wp-json/wp-sso-provider/v1`; public
protocol endpoints are registered as rewrite-backed paths so issuer URLs do not
depend on the REST prefix. Exact endpoint schemas live with route tests.

Initial management endpoints include `GET /wp-json/wp-sso-provider/v1/status`,
`GET /wp-json/wp-sso-provider/v1/applications`, and
`POST /wp-json/wp-sso-provider/v1/applications`.

## Data Models

All identifiers are opaque UUID/ULID strings unless noted. Every row has a site
scope, created/updated timestamps, and appropriate unique/index constraints.

| Entity | Required fields | Security constraint |
|---|---|---|
| Application | id, name, type, status, owner, settings, version | optimistic locking; active only after test |
| Credential | id, application_id, kind, verifier/ciphertext, expires, state | never plaintext at rest after display |
| Session | id_hash, user_id, auth_time, methods, strength, idle/absolute expiry | revocable; shared-store safe |
| AuthorizationCode | code_hash, app/user/session, redirect, scopes, PKCE, expires, consumed | atomic conditional consume |
| RefreshFamily | family/token hashes, generation, app/user/session, expiry, revoked | reuse revokes family |
| Consent | app/user, scopes, claims, granted/revoked times | unique active grant |
| Group/Membership | group id/name; group/user link/source | scoped uniqueness |
| Key | id/kid, purpose, algorithm, encrypted private, public, lifecycle times | AEAD and overlap |
| OneTimeCredential | type, value_hash, context, expiry, consumed | atomic conditional consume |
| AuditEvent | event id/type/result, actor/subject/app, request id, reduced context | append-only; no secrets |
| SecurityEvent | event id/type/severity/confidence/context | explainable classification |

## Out of Scope

- OS-1: OAuth implicit and resource-owner-password grants: prohibited by current
  security guidance.
- OS-2: `alg=none`, SHA-1, wildcard redirects, arbitrary CAS services, and
  administrator-supplied executable policy/claim code: prohibited.
- OS-3: WS-Federation production status, Kerberos cryptography in PHP, and FAPI
  certification: unavailable until separate independently tested profiles exist.
- OS-4: Production claims for any adapter whose conformance/interoperability
  evidence is absent. Code may be present but status remains Experimental/Beta.
