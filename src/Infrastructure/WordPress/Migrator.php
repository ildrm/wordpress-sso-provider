<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress;

use RuntimeException;
use wpdb;

final class Migrator
{
    private const VERSION = '1';

    public function migrate(): void
    {
        if (get_option('wp_sso_provider_schema_version') === self::VERSION) {
            return;
        }

        $wpdb = $this->database();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;
        $sql = $this->schema($prefix, $charset);
        dbDelta($sql);

        update_option('wp_sso_provider_schema_version', self::VERSION, false);
    }

    private function database(): wpdb
    {
        global $wpdb;
        if (! $wpdb instanceof wpdb) {
            throw new RuntimeException('WordPress database is unavailable.');
        }

        return $wpdb;
    }

    /** @return list<string> */
    private function schema(string $prefix, string $charset): array
    {
        return [
            "CREATE TABLE {$prefix}sso_applications (
                id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                client_id varchar(191) NOT NULL,
                client_secret_hash varchar(255) NULL,
                name varchar(191) NOT NULL,
                type varchar(32) NOT NULL,
                status varchar(24) NOT NULL DEFAULT 'draft',
                trusted tinyint(1) NOT NULL DEFAULT 0,
                redirect_uris longtext NOT NULL,
                allowed_scopes longtext NOT NULL,
                settings longtext NOT NULL,
                owner_user_id bigint(20) unsigned NOT NULL,
                last_tested_at datetime NULL,
                last_test_success tinyint(1) NOT NULL DEFAULT 0,
                version bigint(20) unsigned NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY client_site (site_id,client_id),
                KEY status_site (site_id,status)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_sessions (
                id_hash char(64) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                auth_time datetime NOT NULL,
                last_activity_at datetime NOT NULL,
                absolute_expires_at datetime NOT NULL,
                idle_expires_at datetime NOT NULL,
                methods longtext NOT NULL,
                strength smallint unsigned NOT NULL,
                user_agent_hash char(64) NULL,
                ip_prefix_hash char(64) NULL,
                revoked_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id_hash),
                KEY user_active (site_id,user_id,revoked_at),
                KEY expiry (absolute_expires_at,idle_expires_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_auth_codes (
                code_hash char(64) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                application_id char(36) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                session_id_hash char(64) NOT NULL,
                redirect_uri text NOT NULL,
                scopes longtext NOT NULL,
                nonce varchar(255) NULL,
                pkce_challenge varchar(128) NOT NULL,
                expires_at datetime NOT NULL,
                consumed_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (code_hash),
                KEY app_expiry (site_id,application_id,expires_at),
                KEY cleanup (expires_at,consumed_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_refresh_tokens (
                token_hash char(64) NOT NULL,
                family_id char(36) NOT NULL,
                generation int unsigned NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                application_id char(36) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                session_id_hash char(64) NULL,
                scopes longtext NOT NULL,
                expires_at datetime NOT NULL,
                consumed_at datetime NULL,
                revoked_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (token_hash),
                UNIQUE KEY family_generation (family_id,generation),
                KEY app_user (site_id,application_id,user_id),
                KEY cleanup (expires_at,revoked_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_access_tokens (
                token_hash char(64) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                application_id char(36) NOT NULL,
                user_id bigint(20) unsigned NULL,
                session_id_hash char(64) NULL,
                scopes longtext NOT NULL,
                audience varchar(191) NOT NULL,
                expires_at datetime NOT NULL,
                revoked_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (token_hash),
                KEY app_expiry (site_id,application_id,expires_at),
                KEY user_active (site_id,user_id,revoked_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_consents (
                id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                application_id char(36) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                scopes longtext NOT NULL,
                claims longtext NOT NULL,
                granted_at datetime NOT NULL,
                last_used_at datetime NULL,
                revoked_at datetime NULL,
                PRIMARY KEY  (id),
                KEY app_user (site_id,application_id,user_id,revoked_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_keys (
                id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                kid varchar(191) NOT NULL,
                purpose varchar(32) NOT NULL,
                algorithm varchar(32) NOT NULL,
                encrypted_private longtext NOT NULL,
                public_material longtext NOT NULL,
                status varchar(24) NOT NULL,
                activates_at datetime NOT NULL,
                expires_at datetime NULL,
                retires_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY kid_site (site_id,kid),
                KEY active_key (site_id,purpose,status,activates_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_groups (
                id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                name varchar(191) NOT NULL,
                display_name varchar(191) NOT NULL,
                description text NULL,
                rule_definition longtext NULL,
                version bigint(20) unsigned NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY name_site (site_id,name)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_group_members (
                group_id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                source varchar(32) NOT NULL DEFAULT 'explicit',
                created_at datetime NOT NULL,
                PRIMARY KEY  (group_id,user_id,source),
                KEY user_groups (site_id,user_id)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_cas_tickets (
                ticket_hash char(64) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                application_id char(36) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                service_uri text NOT NULL,
                expires_at datetime NOT NULL,
                consumed_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (ticket_hash),
                KEY cleanup (expires_at,consumed_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_replay (
                value_hash char(64) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                purpose varchar(48) NOT NULL,
                expires_at datetime NOT NULL,
                consumed_at datetime NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (value_hash,purpose,site_id),
                KEY cleanup (expires_at,consumed_at)
            ) {$charset};",
            "CREATE TABLE {$prefix}sso_audit_events (
                id char(36) NOT NULL,
                site_id bigint(20) unsigned NOT NULL,
                event_type varchar(64) NOT NULL,
                result varchar(24) NOT NULL,
                severity varchar(16) NOT NULL DEFAULT 'info',
                actor_user_id bigint(20) unsigned NULL,
                subject_user_id bigint(20) unsigned NULL,
                application_id char(36) NULL,
                protocol varchar(24) NULL,
                request_id char(36) NOT NULL,
                reason varchar(191) NULL,
                context longtext NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY activity (site_id,created_at),
                KEY event_type (site_id,event_type,created_at),
                KEY application (site_id,application_id,created_at)
            ) {$charset};",
        ];
    }
}
