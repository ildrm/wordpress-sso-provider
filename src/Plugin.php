<?php

declare(strict_types=1);

namespace WpSsoProvider;

use WpSsoProvider\Admin\AdminPage;
use WpSsoProvider\Application\ApplicationService;
use WpSsoProvider\Infrastructure\WordPress\DatabaseApplicationRepository;
use WpSsoProvider\Infrastructure\WordPress\HealthChecks;
use WpSsoProvider\Infrastructure\WordPress\Rest\ApplicationsController;
use WpSsoProvider\Infrastructure\WordPress\Rest\StatusController;
use WpSsoProvider\Protocol\OAuth\RedirectUriValidator;
use WpSsoProvider\Security\IdGenerator;
use WpSsoProvider\Security\TokenGenerator;

final class Plugin
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        add_action('plugins_loaded', [self::class, 'loadTextDomain']);
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);
        add_action('admin_menu', [self::class, 'registerAdmin']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        add_filter('site_status_tests', [HealthChecks::class, 'register']);
    }

    public static function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'wordpress-sso-provider',
            false,
            dirname(plugin_basename(WP_SSO_PROVIDER_FILE)) . '/languages'
        );
    }

    public static function registerRestRoutes(): void
    {
        $repository = new DatabaseApplicationRepository();
        $service = new ApplicationService(
            $repository,
            new RedirectUriValidator(),
            new TokenGenerator(),
            new IdGenerator()
        );

        (new StatusController())->register();
        (new ApplicationsController($service))->register();
    }

    public static function registerAdmin(): void
    {
        (new AdminPage(new DatabaseApplicationRepository()))->register();
    }

    public static function enqueueAdminAssets(string $hook): void
    {
        if (! str_starts_with($hook, 'toplevel_page_wp-sso-provider') && ! str_contains($hook, 'wp-sso-provider')) {
            return;
        }

        wp_enqueue_style(
            'wp-sso-provider-admin',
            plugins_url('admin/build/admin.css', WP_SSO_PROVIDER_FILE),
            [],
            WP_SSO_PROVIDER_VERSION
        );
    }
}
