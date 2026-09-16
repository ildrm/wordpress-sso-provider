# Testing and evidence

Evidence recorded on 2026-09-16:

| Gate | Result | Detail |
|---|---|---|
| Spec validation | PASS | 100/100, no warnings |
| PHPUnit | PASS | 21 tests, 54 assertions on PHP 8.5 |
| PHP syntax | PASS | All project PHP files |
| PHPStan | PASS | Level max across core and WordPress adapters with WP 7.1 stubs |
| PHPCS | PASS | PSR-12 plus targeted official WPCS security/DB/i18n rules |
| Composer audit | PASS | No production advisories |
| WordPress activation | PASS | WordPress 7.1.0 / PHP 8.3 / MariaDB 11.4 |
| Migration | PASS | 12 tables; repeat activation preserved version |
| REST permissions | PASS | Admin allowed; anonymous listing rejected |
| Client-secret storage | PASS | Returned once, only password verifier persisted |
| Redirect negative test | PASS | Wildcard rejected |
| Authorization-code race | PASS | Two real processes; exactly one consumer |
| CAS ticket binding/reuse | PASS | Wrong service and second use rejected |
| Browser E2E / accessibility | NOT RUN | Admin shell not feature-complete |
| OIDC conformance | NOT RUN | Public OP deliberately not exposed |
| Independent SAML/SCIM/CAS clients | NOT RUN | Adapters not exposed |
| Performance | NOT RUN | No public protocol workload |

Browser, accessibility, conformance, performance, and full stylistic WPCS review
remain release blockers for production protocol status. The targeted WPCS gate
covers prepared SQL, restricted/development functions, output escaping, nonce and
input handling, and internationalization in WordPress-facing adapters.
