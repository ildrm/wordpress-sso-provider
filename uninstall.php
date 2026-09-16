<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Identity infrastructure is preserved unless a network/site administrator has
// explicitly opted into destructive removal before uninstalling.
if (! defined('WP_SSO_REMOVE_DATA_ON_UNINSTALL') || WP_SSO_REMOVE_DATA_ON_UNINSTALL !== true) {
    return;
}

global $wpdb;
if (! $wpdb instanceof wpdb) {
    return;
}

$tables = [
    'sso_group_members',
    'sso_groups',
    'sso_consents',
    'sso_access_tokens',
    'sso_refresh_tokens',
    'sso_auth_codes',
    'sso_cas_tickets',
    'sso_replay',
    'sso_sessions',
    'sso_keys',
    'sso_audit_events',
    'sso_applications',
];

foreach ($tables as $suffix) {
    $table = $wpdb->prefix . $suffix;
    // Identifiers come exclusively from the fixed allowlist above.
    $wpdb->query("DROP TABLE IF EXISTS `{$table}`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option('wp_sso_provider_schema_version');
delete_option('wp_sso_provider_installed_at');
