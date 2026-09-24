# Implementation audit, 2026-09-24

The plugin is a pre-release IAM foundation. The only registered REST routes are
the capability-protected status and application-management routes. Application
creation leaves every client in `draft`; there is no activation route or public
protocol endpoint. Database tables, locked dependencies, and protocol models do
not make OAuth, OIDC, federation, provisioning, or resource-server flows usable.

## Findings and changes in this pass

- Redirect registration accepted loopback HTTP for web clients, accepted
  `localhost` as a loopback literal, and had no native private-scheme support.
  Matching was exact for every client, including native loopback ports. The
  validator now applies client-type rules and the RFC 8252 port exception.
  Domain ownership and iOS/Android app-link association are not verified.
- MySQL text collation could equate distinct redirect or CAS service strings
  during one-time credential consumption. Conditional updates now compare
  these values as binary strings.
- Sessions remained valid at the exact idle and absolute expiry timestamps,
  and even before creation. The validity interval is now bounded by creation
  and exclusive expiry timestamps.
- Site Health and the status route reported any master key of at least 32 bytes
  as ready, while the encryption primitive requires exactly 32 bytes. These
  checks now agree.

## Remaining release blockers

Authorization and token endpoints, client lifecycle activation, consent,
refresh-token rotation, revocation, introspection, protected REST resource
authentication, OIDC ID tokens/JWKS/UserInfo/logout, device flow, DPoP, PAR,
federation, passkeys/MFA, rate limits, admin workflows, and conformance evidence
are absent. No protocol should be described as production supported. The
locked SAML, TOTP, and WebAuthn dependencies do not yet have adapters.

The WordPress Docker smoke test passed with new cases for native public-client
registration and binary callback matching. Two concurrent authorization-code
consumers yielded one success and one rejection. Independent client and official
conformance tests have not been run. See [protocol support](PROTOCOL_SUPPORT.md)
for the evidence table.

## Upgrade notes

No database schema migration or public endpoint was added in this pass. Existing
application IDs and records are preserved. Previously registered `localhost` or
web-client HTTP redirects fail the new validator if revalidated; review such
draft records before any future activation workflow.
The configured raw `WP_SSO_MASTER_KEY` must be exactly 32 bytes to match the
encryption primitive. The Docker test configuration now supplies this value to
both WordPress and WP-CLI containers.
