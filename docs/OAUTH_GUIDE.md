# OAuth guide

Status: **Experimental, not exposed.** Version 0.1.0 implements tested exact
redirect validation, S256 PKCE, scope parsing, opaque token generation/hashing,
and atomic authorization-code persistence. It does not register authorization,
token, revocation, introspection, device, or client-credentials endpoints.

Implicit and resource-owner-password grants are permanently excluded. Do not
configure an OAuth client against this release.

