# Developer guide

Runtime code uses the `WpSsoProvider` PSR-4 namespace. Put framework-independent
entities/value objects in `src/Domain`, use cases and ports in `src/Application`,
and WordPress implementations in `src/Infrastructure/WordPress`. Protocol code is
an adapter and must not query `WP_User`, user meta, or the database directly.

Constructor injection is required. New policy and claim behavior must use closed,
typed operator registries; administrator-entered PHP or JavaScript is prohibited.
One-time credentials must use a single conditional database mutation and include a
two-process race test.

Run the commands in the root README before submitting a change. Update
`docs/REQUIREMENTS_TRACEABILITY.md` and `docs/PROTOCOL_SUPPORT.md` with actual,
not anticipated, evidence.

