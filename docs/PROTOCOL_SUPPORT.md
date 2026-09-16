# Protocol support matrix

| Protocol | Implementation | Unit | WP integration | Independent client | Conformance | Negative security | Status |
|---|---|---:|---:|---:|---:|---:|---|
| OAuth 2.0 | Core PKCE/redirect/scope/atomic code primitives | PASS | Partial PASS | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| OpenID Connect | Discovery model only | PASS | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| SAML 2.0 | Audited dependencies locked; adapter absent | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| SCIM 2.0 | Safe filter subset only | PASS | NOT RUN | NOT RUN | NOT RUN | Partial PASS | Experimental, not exposed |
| CAS | Atomic service-bound ticket persistence | PASS | PASS | NOT RUN | N/A | Partial PASS | Experimental, not exposed |
| WS-Federation | None | NOT RUN | NOT RUN | NOT RUN | NOT RUN | NOT RUN | Unsupported |
| LDAP/upstream | Boundary specified; connector absent | NOT RUN | NOT RUN | NOT RUN | N/A | NOT RUN | Unsupported |
| Kerberos/SPNEGO | Trusted-proxy boundary specified; adapter absent | NOT RUN | NOT RUN | NOT RUN | N/A | NOT RUN | Unsupported |

“Production Supported” is intentionally absent. That status requires the relevant
independent client, conformance where available, complete negative campaign,
browser/accessibility verification, and three clean final reviews.

