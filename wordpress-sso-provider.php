<?php

/**
 * Plugin Name: WordPress SSO Provider
 * Plugin URI:  https://github.com/ildrm/wordpress-sso-provider
 * Description: Standards-based identity, SSO, authorization, and provisioning provider for WordPress.
 * Version:     0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Author:      Shahin Ilderemi
 * Author URI:  https://ildrm.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wordpress-sso-provider
 * Domain Path: /languages
 */

declare(strict_types=1);

use WpSsoProvider\Infrastructure\WordPress\Installer;
use WpSsoProvider\Plugin;

if (! defined('ABSPATH')) {
    exit;
}

define('WP_SSO_PROVIDER_VERSION', '0.1.0');
define('WP_SSO_PROVIDER_FILE', __FILE__);
define('WP_SSO_PROVIDER_DIR', __DIR__);

$wp_sso_autoloader = __DIR__ . '/vendor/autoload.php';
if (! is_readable($wp_sso_autoloader)) {
    add_action(
        'admin_notices',
        static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('WordPress SSO Provider cannot start because its production dependencies are missing.', 'wordpress-sso-provider');
            echo '</p></div>';
        }
    );
    return;
}

require $wp_sso_autoloader;

register_activation_hook(__FILE__, [Installer::class, 'activate']);
register_deactivation_hook(__FILE__, [Installer::class, 'deactivate']);

Plugin::boot();
