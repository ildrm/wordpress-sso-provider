# Protocol support matrix

| Protocol | Implementation | Unit | WP integration | E2E | Independent client | Conformance | Negative security | Status |
|---|---|---:|---:|---:|---:|---:|---:|---|
| OAuth 2.0 | PKCE, type-aware redirects, scope and atomic code primitives; no public flow | PASS | Partial PASS | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| Native OAuth redirects (RFC 8252) | HTTPS URI registration, reverse-domain private scheme, literal loopback port exception; app-link ownership unverified | PASS | PASS | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Registration only |
| OpenID Connect | Discovery model only; no published metadata or endpoints | PASS | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| Bearer resource server | None | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| DPoP, Device Flow, PAR, JAR, JARM, CIBA, token exchange | None | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| Passkeys and MFA | Dependencies and session model only | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| SAML 2.0 | Dependencies locked; adapter absent | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| SCIM 2.0 | Safe filter subset only | PASS | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| CAS | Atomic service-bound ticket persistence | PASS | PASS | NOT RUN | NOT RUN | N/A | Partial PASS | Experimental, not exposed |
| WS-Federation | None | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| LDAP/upstream | Boundary specified; connector absent | NOT RUN | NOT RUN | NOT RUN | NOT RUN | N/A | NOT RUN | Unsupported |
| Kerberos/SPNEGO | Trusted-proxy boundary specified; adapter absent | NOT RUN | NOT RUN | NOT RUN | NOT RUN | N/A | NOT RUN | Unsupported |

“Production Supported” is intentionally absent. That status requires the relevant
independent client, conformance where available, complete negative campaign,
browser/accessibility verification, and three clean final reviews.

As checked on 2026-09-24, [OAuth 2.1 is an active Internet-Draft](https://datatracker.ietf.org/doc/draft-ietf-oauth-v2-1/),
not an RFC. [OpenID Connect Native SSO for Mobile Apps is an Implementer's Draft](https://openid.net/second-implementers-draft-openid-connect-native-sso-for-mobile-apps-approved/),
not a Final Specification. Neither profile is implemented or advertised here.

