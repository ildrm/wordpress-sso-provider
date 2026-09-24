# OAuth guide

Status: **Experimental, not exposed.** The repository implements type-aware
redirect registration and matching, S256 PKCE verification, scope parsing,
opaque token generation/hashing, and atomic authorization-code persistence.
Literal loopback redirects can vary only by port for native and desktop
clients. It does not register authorization,
token, revocation, introspection, device, or client-credentials endpoints.

Implicit and resource-owner-password grants are permanently excluded. Do not
configure an OAuth client against this release.

