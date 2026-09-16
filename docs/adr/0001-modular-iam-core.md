# ADR 0001: Modular IAM core with protocol ports

Status: Accepted — 2026-09-16

## Context

The product shares identities, applications, policy, sessions, consent, claims,
keys, replay protection, and audit across OAuth/OIDC, SAML, SCIM, and CAS. WordPress
is both host runtime and canonical local user store.

## Decision

Use a modular monolith with domain/application/infrastructure layers and narrow
protocol adapters. Domain code is WordPress-independent. WordPress repositories,
HTTP routing, cron, capabilities, and cache implementations satisfy explicit
ports through constructor injection. Dedicated relational tables hold high-volume
and transactional IAM data. Site scope is mandatory in persistence keys.

The database is the correctness baseline. Object cache may accelerate immutable
configuration and public metadata but never becomes the sole authority for one-time
credential consumption or authorization decisions.

## Consequences

One installable plugin remains operationally simple. Protocols cannot bypass core
policy/session/audit controls. More interfaces and mapping code are accepted in
exchange for testability and security review. Services may be extracted only if a
future deployment boundary is independently justified.

