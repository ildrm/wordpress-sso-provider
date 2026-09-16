# Upgrade guide

Version 0.1.0 uses schema version `1`. Migrations are idempotent and run during
activation. Deactivation and reactivation preserve all records. Before future
upgrades, take a database backup and review release-specific migration notes.

Never delete IAM tables as a routine upgrade step. Destructive uninstall requires
the explicit `WP_SSO_REMOVE_DATA_ON_UNINSTALL` constant and is irreversible without
a backup.

