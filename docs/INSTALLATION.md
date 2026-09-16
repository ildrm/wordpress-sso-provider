# Installation

## Development install

1. Install PHP 8.2+, Composer 2, required extensions, WordPress 6.6+, and a
   supported MySQL/MariaDB database.
2. Run `composer install --no-dev --classmap-authoritative` for production-like
   dependencies.
3. Place the project directory at
   `wp-content/plugins/wordpress-sso-provider`.
4. Define a dedicated secret outside the database:

   ```php
   define('WP_SSO_MASTER_KEY', getenv('WP_SSO_MASTER_KEY'));
   ```

   The environment value must contain at least 32 random bytes and must not reuse
   WordPress salts.
5. Activate “WordPress SSO Provider” and open **SSO Provider → Diagnostics**.

Activation creates site-prefixed tables and grants three plugin capabilities to
the Administrator role. Deactivation preserves all identity data. Uninstall also
preserves data unless `WP_SSO_REMOVE_DATA_ON_UNINSTALL` is explicitly `true`.

## Release ZIP

Only use the ZIP identified in `docs/RELEASE.md`; do not package the repository
directly because tests, development dependencies, and the Docker harness must not
ship in production.

