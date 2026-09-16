<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress;

final class Installer
{
    public static function activate(bool $networkWide = false): void
    {
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            deactivate_plugins(plugin_basename(WP_SSO_PROVIDER_FILE));
            wp_die(esc_html__('WordPress SSO Provider requires PHP 8.2 or newer.', 'wordpress-sso-provider'));
        }

        if (is_multisite() && $networkWide) {
            $siteIds = get_sites(['fields' => 'ids', 'number' => 0]);
            foreach ($siteIds as $siteId) {
                switch_to_blog((int) $siteId);
                self::activateSite();
                restore_current_blog();
            }
        } else {
            self::activateSite();
        }

        flush_rewrite_rules(false);
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('wp_sso_provider_cleanup');
        flush_rewrite_rules(false);
    }

    private static function activateSite(): void
    {
        (new Migrator())->migrate();
        self::installCapabilities();

        if (get_option('wp_sso_provider_installed_at') === false) {
            add_option('wp_sso_provider_installed_at', gmdate('c'), '', false);
        }

        if (! wp_next_scheduled('wp_sso_provider_cleanup')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'wp_sso_provider_cleanup');
        }
    }

    private static function installCapabilities(): void
    {
        $administrator = get_role('administrator');
        if ($administrator === null) {
            return;
        }

        foreach (['manage_sso_provider', 'view_sso_audit', 'manage_sso_security'] as $capability) {
            $administrator->add_cap($capability);
        }
    }
}
