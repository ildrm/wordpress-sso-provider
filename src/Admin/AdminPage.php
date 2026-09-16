<?php

declare(strict_types=1);

namespace WpSsoProvider\Admin;

use WpSsoProvider\Application\ApplicationRepository;

final class AdminPage
{
    public function __construct(private readonly ApplicationRepository $applications)
    {
    }

    public function register(): void
    {
        add_menu_page(
            __('SSO Provider', 'wordpress-sso-provider'),
            __('SSO Provider', 'wordpress-sso-provider'),
            'manage_sso_provider',
            'wp-sso-provider',
            [$this, 'renderDashboard'],
            'dashicons-shield-alt',
            58
        );

        $sections = [
            'wp-sso-provider' => __('Dashboard', 'wordpress-sso-provider'),
            'wp-sso-provider-applications' => __('Applications', 'wordpress-sso-provider'),
            'wp-sso-provider-directory' => __('Directory', 'wordpress-sso-provider'),
            'wp-sso-provider-access' => __('Access', 'wordpress-sso-provider'),
            'wp-sso-provider-security' => __('Security', 'wordpress-sso-provider'),
            'wp-sso-provider-provisioning' => __('Provisioning', 'wordpress-sso-provider'),
            'wp-sso-provider-activity' => __('Activity', 'wordpress-sso-provider'),
            'wp-sso-provider-diagnostics' => __('Diagnostics', 'wordpress-sso-provider'),
            'wp-sso-provider-settings' => __('Settings', 'wordpress-sso-provider'),
        ];

        foreach ($sections as $slug => $label) {
            if ($slug === 'wp-sso-provider') {
                add_submenu_page($slug, $label, $label, 'manage_sso_provider', $slug, [$this, 'renderDashboard']);
                continue;
            }
            add_submenu_page('wp-sso-provider', $label, $label, 'manage_sso_provider', $slug, [$this, 'renderSection']);
        }
    }

    public function renderDashboard(): void
    {
        $items = $this->applications->listForSite(get_current_blog_id());
        $active = count(array_filter($items, static fn (array $item): bool => $item['status'] === 'active'));
        ?>
        <main class="wrap wsp-admin" aria-labelledby="wsp-page-title">
            <h1 id="wsp-page-title"><?php esc_html_e('SSO Provider', 'wordpress-sso-provider'); ?></h1>
            <p class="wsp-lead"><?php esc_html_e('Connect applications to one shared identity, policy, session, and security layer.', 'wordpress-sso-provider'); ?></p>
            <div class="wsp-status" role="status">
                <span aria-hidden="true">!</span>
                <strong><?php esc_html_e('Setup required', 'wordpress-sso-provider'); ?></strong>
                <span><?php esc_html_e('Configure the dedicated master key before enabling token-signing protocols.', 'wordpress-sso-provider'); ?></span>
            </div>
            <section aria-labelledby="wsp-overview-title">
                <h2 id="wsp-overview-title"><?php esc_html_e('Overview', 'wordpress-sso-provider'); ?></h2>
                <div class="wsp-grid">
                    <article class="wsp-card"><span><?php esc_html_e('Applications', 'wordpress-sso-provider'); ?></span><strong><?php echo esc_html((string) count($items)); ?></strong></article>
                    <article class="wsp-card"><span><?php esc_html_e('Active', 'wordpress-sso-provider'); ?></span><strong><?php echo esc_html((string) $active); ?></strong></article>
                    <article class="wsp-card"><span><?php esc_html_e('Protocol exposure', 'wordpress-sso-provider'); ?></span><strong><?php esc_html_e('Off', 'wordpress-sso-provider'); ?></strong></article>
                </div>
            </section>
            <section class="wsp-card wsp-setup" aria-labelledby="wsp-setup-title">
                <h2 id="wsp-setup-title"><?php esc_html_e('Get ready for your first application', 'wordpress-sso-provider'); ?></h2>
                <ol>
                    <li><?php esc_html_e('Review system readiness in Diagnostics.', 'wordpress-sso-provider'); ?></li>
                    <li><?php esc_html_e('Configure the master key and security baseline.', 'wordpress-sso-provider'); ?></li>
                    <li><?php esc_html_e('Create and test an application before activation.', 'wordpress-sso-provider'); ?></li>
                </ol>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=wp-sso-provider-diagnostics')); ?>"><?php esc_html_e('Review diagnostics', 'wordpress-sso-provider'); ?></a>
            </section>
        </main>
        <?php
    }

    public function renderSection(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Read-only route is unslashed and sanitized on the next line.
        $pageInput = $_GET['page'] ?? '';
        $page = is_string($pageInput) ? sanitize_key(wp_unslash($pageInput)) : '';
        $label = ucwords(str_replace(['wp-sso-provider-', '-'], ['', ' '], $page));
        ?>
        <main class="wrap wsp-admin" aria-labelledby="wsp-page-title">
            <h1 id="wsp-page-title"><?php echo esc_html($label); ?></h1>
            <div class="wsp-card">
                <h2><?php esc_html_e('Foundation installed', 'wordpress-sso-provider'); ?></h2>
                <p><?php esc_html_e('This surface is intentionally unavailable until its application service and verification gates are complete.', 'wordpress-sso-provider'); ?></p>
            </div>
        </main>
        <?php
    }
}
