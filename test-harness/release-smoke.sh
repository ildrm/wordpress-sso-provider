#!/bin/sh
set -eu

wp_cli="wp --allow-root --path=/var/www/html"

until $wp_cli core is-installed >/dev/null 2>&1; do
    if $wp_cli core install \
        --url=http://localhost:8889 \
        --title='SSO Release Test' \
        --admin_user=admin \
        --admin_password='local-release-test-password' \
        --admin_email=admin@example.test \
        --skip-email >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

$wp_cli plugin install /artifacts/wordpress-sso-provider-0.1.0.zip --activate --force
$wp_cli eval 'global $wpdb; $tables = $wpdb->get_col("SHOW TABLES LIKE \"{$wpdb->prefix}sso_%\""); if (count($tables) !== 12) { throw new RuntimeException("Release migration incomplete"); }'
$wp_cli eval-file /test-harness/wordpress-integration.php
$wp_cli plugin deactivate wordpress-sso-provider
$wp_cli plugin activate wordpress-sso-provider
$wp_cli eval 'if (get_option("wp_sso_provider_schema_version") !== "1") { throw new RuntimeException("Release configuration integrity failed"); }'

echo 'Exact release ZIP installation test passed.'

