#!/bin/sh
set -eu

wp_cli="wp --allow-root --path=/var/www/html"

until $wp_cli core is-installed >/dev/null 2>&1; do
    if $wp_cli core install \
        --url=http://localhost:8888 \
        --title='SSO Test' \
        --admin_user=admin \
        --admin_password='local-test-password' \
        --admin_email=admin@example.test \
        --skip-email >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

$wp_cli plugin activate wordpress-sso-provider
$wp_cli eval 'if (get_option("wp_sso_provider_schema_version") !== "1") { throw new RuntimeException("Migration version missing"); }'
$wp_cli eval 'global $wpdb; $tables = $wpdb->get_col("SHOW TABLES LIKE \"{$wpdb->prefix}sso_%\""); if (count($tables) !== 12) { throw new RuntimeException("Expected 12 SSO tables, found " . count($tables)); }'
$wp_cli eval 'if (!get_role("administrator")->has_cap("manage_sso_provider")) { throw new RuntimeException("Capability missing"); }'
$wp_cli eval-file /var/www/html/wp-content/plugins/wordpress-sso-provider/test-harness/wordpress-integration.php
$wp_cli plugin deactivate wordpress-sso-provider
$wp_cli plugin activate wordpress-sso-provider
$wp_cli eval 'if (get_option("wp_sso_provider_schema_version") !== "1") { throw new RuntimeException("Migration lost after reactivation"); }'

echo 'WordPress activation smoke test passed.'
