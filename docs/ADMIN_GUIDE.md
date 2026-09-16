# Administrator guide

The navigation is application-centric: Dashboard, Applications, Directory,
Access, Security, Provisioning, Activity, Diagnostics, and Settings.

At version 0.1.0 the Dashboard and readiness checks are usable. Applications can
be created through the protected management REST API and are always created as
`draft`. The secret for confidential clients is returned once; only a password
verifier remains in the database. Activation is not exposed because protocol
connection tests and full application editing are not yet complete.

Protocol pages intentionally do not appear as primary navigation. Public protocol
endpoints are off, so this release cannot accidentally become an unverified IdP.

