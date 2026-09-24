# Requirements traceability matrix

Status values: `PLANNED`, `PARTIAL`, `IMPLEMENTED`, `PASS`, `FAIL`, `BLOCKED`, `NOT RUN`,
`NOT APPLICABLE`. Code alone never changes a row to `PASS`.

| ID | Requirement | Priority | Subsystem | Specification | Planned implementation | Verification | Security focus | Status |
|---|---|---|---|---|---|---|---|---|
| RQ-001 | Install, lifecycle, migrations, uninstall | P0 | Infrastructure | WP Plugin Handbook | bootstrap, Migrator, uninstall | integration + ZIP install | data loss/capability | PASS |
| RQ-002 | Protocol-neutral IAM core | P0 | Domain/Application | ADR-0001 | ports, services, DTOs | unit + architecture | adapter bypass | PARTIAL |
| RQ-003 | Canonical identity | P0 | Identity | OIDC Core/SCIM schema | IdentityProvider | unit + integration | enumeration/data minimization | PLANNED |
| RQ-004 | Application lifecycle/types/catalog | P0 | Applications | product spec FR-3 | ApplicationService/repository | unit + REST/E2E | unsafe activation | PARTIAL |
| RQ-005 | Claims/scopes/groups | P0 | Authorization | OIDC Core/RFC 7643 | engines/repositories | unit + protocol | over-disclosure/eval | PARTIAL |
| RQ-006 | Policy and consent | P0 | Authorization | RFC 9700/OIDC Core | PolicyEngine/ConsentService | unit + E2E | consent expansion/bypass | PARTIAL |
| RQ-007 | SSO sessions/logout | P0 | Authentication | OIDC logout/SAML/CAS | SessionService/store | concurrency + E2E | fixation/revocation race | PARTIAL |
| RQ-008 | Key/certificate/secret lifecycle | P0 | Security | JOSE/XMLSig/libsodium | KeyService/SecretService | unit + rotation + scan | key disclosure/downgrade | PARTIAL |
| RQ-009 | OAuth authorization server | P0 | OAuth | RFC 6749/7636/7009/7662/8414/9700 | OAuth adapter/use cases | unit + protocol + OIDF | redirect/replay/mix-up | PARTIAL |
| RQ-010 | OpenID Provider | P0 | OIDC | OIDC Core/Discovery errata2/logout | OIDC adapter | protocol + RP + OIDF | nonce/alg/logout redirect | PARTIAL |
| RQ-011 | SAML 2.0 IdP | P0 | SAML | OASIS SAML 2.0 + errata | library adapter | SimpleSAML/Shibboleth/xmlsec | XXE/XSW/replay | PLANNED |
| RQ-012 | SCIM 2.0 provider | P0 | SCIM | RFC 7643/7644/9865 | SCIM adapter | contract + independent tester | IDOR/filter DoS | PARTIAL |
| RQ-013 | CAS provider | P0 | CAS | Apereo CAS 3.0 | CAS adapter | phpCAS + Java client | ticket/open redirect/replay | PARTIAL |
| RQ-014 | WS-Federation passive adapter | P2 | Federation | OASIS WS-Fed | isolated adapter | .NET RP | signature/realm/reply | PLANNED |
| RQ-015 | LDAP/AD identity source | P1 | Upstream | LDAP RFCs | connector | OpenLDAP/Samba | injection/TLS/secret | PLANNED |
| RQ-016 | Kerberos/SPNEGO boundary | P1 | Upstream | RFC 4559/deployment | trusted adapter | proxy harness | spoofed header | PLANNED |
| RQ-017 | Password/TOTP/recovery/WebAuthn/step-up | P0 | Authentication | RFC 6238/WebAuthn L3 | auth methods | unit + virtual authenticator | secret/challenge replay | PLANNED |
| RQ-018 | Atomic replay protection | P0 | Security | protocol requirements | ReplayStore | parallel DB tests | check-then-act race | PASS |
| RQ-019 | Audit/security events/retention | P0 | Observability | product spec | event stores/cron | unit + secret scan | log injection/leakage | PARTIAL |
| RQ-020 | Rate limiting | P0 | Security | RFC 9700/OWASP | limiter port/store | unit + load/security | lockout DoS | PLANNED |
| RQ-021 | SSRF-safe fetch/webhooks | P0 | Security | RFC 3986/BCP guidance | SafeHttpClient | unit + rebinding fixtures | SSRF/credential forwarding | PLANNED |
| RQ-022 | REST management and WP-CLI | P1 | Management | WP REST/CLI docs | controllers/commands | integration | capability/CSRF/IDOR | PARTIAL |
| RQ-023 | Application-centric admin + setup | P1 | Admin UI | WCAG 2.2 AA | React/TS app | component/E2E/axe/manual | XSS/unsafe defaults | PARTIAL |
| RQ-024 | Diagnostics/Site Health/self-service | P1 | Operations | WP APIs | services/screens | integration/E2E | secret exposure/IDOR | PARTIAL |
| RQ-025 | Multisite/HA/cache/cron/privacy | P1 | Infrastructure | WP APIs | scoped repos/stores | matrix tests | cross-site leakage | PARTIAL |
| RQ-026 | Extensions/templates | P2 | Extensibility | internal contracts | registry/interfaces | contract tests | validation bypass | PLANNED |
| RQ-027 | Performance/observability | P1 | Operations | NFR-6 | metrics/correlation | k6/profile | cache auth decision | NOT RUN |
| RQ-028 | Static/dependency/license quality | P0 | Build | WPCS/PHPStan/npm | CI config | lint/audit/license | vulnerable dependency | PARTIAL |
| RQ-029 | Unit/integration/protocol/security/E2E | P0 | QA | test plan | test suites/harness | recorded reports | hostile input/races | PARTIAL |
| RQ-030 | Deterministic release/install | P0 | Release | product spec FR-23 | build script | clean ZIP smoke | test secrets/artifacts | PASS |
| RQ-031 | Documentation/support matrix | P1 | Documentation | product spec | docs set | doc review | false claims | PARTIAL |
| RQ-032 | Three clean independent reviews | P0 | Release | master requirement 102 | review records | 3 consecutive passes | Critical/High reset | NOT RUN |

The detailed acceptance criteria and edge cases in `specs/iam-platform.md` are the
test contract. The master prompt sections 1–109 are tracked by RQ-001–RQ-049; the
mapping is thematic rather than duplicating every prose section as a fake feature.

## Additional mobile and protocol requirements

The rows below use the direct `requirement → code → test → evidence` mapping.
`None` means no implementation or verification exists; it is not a passing gate.

| ID | Requirement | Priority | Code | Test | Evidence/status |
|---|---|---|---|---|---|
| RQ-033 | Native redirect categories and exact loopback-port exception | P0 | `RedirectUriValidator`, `ApplicationService` | `OAuthSecurityTest`, `wordpress-integration.php` | Unit and Docker WordPress PASS; no mobile E2E |
| RQ-034 | Bearer WordPress REST authentication | P0 | None | None | Unsupported |
| RQ-035 | Refresh rotation and family reuse revocation | P0 | Schema only | None | Unsupported |
| RQ-036 | DPoP sender constraint | P1 | None | None | Unsupported |
| RQ-037 | Device authorization grant | P1 | None | None | Unsupported |
| RQ-038 | PAR | P1 | None | None | Unsupported |
| RQ-039 | JAR | P1 | None | None | Unsupported |
| RQ-040 | JARM | P1 | None | None | Unsupported |
| RQ-041 | CIBA | P1 | None | None | Unsupported |
| RQ-042 | Native SSO mobile profile | P2 | None | None | Unsupported; ID2 is not Final |
| RQ-043 | Protected resource metadata | P1 | None | None | Unsupported |
| RQ-044 | Token exchange | P1 | None | None | Unsupported |
| RQ-045 | Passkey registration and authentication | P0 | Dependency only | None | Unsupported |
| RQ-046 | Upstream OIDC/SAML federation | P1 | None | None | Unsupported |
| RQ-047 | iOS/Android mobile interoperability | P0 | None | None | NOT RUN; no public flow |
| RQ-048 | OAuth authorization-code PKCE end to end | P0 | Primitives only | Unit only | Unsupported as a public flow |
| RQ-049 | OIDC provider end to end | P0 | Metadata model only | Unit only | Unsupported as a public flow |
