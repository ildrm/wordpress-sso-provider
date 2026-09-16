<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress;

use RuntimeException;
use WpSsoProvider\Application\ApplicationRepository;
use wpdb;

final class DatabaseApplicationRepository implements ApplicationRepository
{
    private readonly wpdb $database;

    public function __construct(?wpdb $database = null)
    {
        if ($database instanceof wpdb) {
            $this->database = $database;
            return;
        }

        global $wpdb;
        if (! $wpdb instanceof wpdb) {
            throw new RuntimeException('WordPress database is unavailable.');
        }
        $this->database = $wpdb;
    }

    public function listForSite(int $siteId): array
    {
        $wpdb = $this->database;
        $table = $wpdb->prefix . 'sso_applications';
        $sql = $wpdb->prepare(
            'SELECT id, client_id, name, type, status, trusted, redirect_uris, allowed_scopes, owner_user_id, last_tested_at, last_test_success, version, created_at, updated_at FROM %i WHERE site_id = %d ORDER BY created_at DESC LIMIT 200',
            $table,
            $siteId
        );
        if (! is_string($sql)) {
            throw new RuntimeException('Unable to prepare the application query.');
        }
        $rows = $wpdb->get_results($sql, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared and type-checked immediately above.

        return array_values(array_map([$this, 'decode'], is_array($rows) ? $rows : []));
    }

    public function insert(array $record): void
    {
        $wpdb = $this->database;
        $table = $wpdb->prefix . 'sso_applications';
        foreach (['redirect_uris', 'allowed_scopes', 'settings'] as $field) {
            $record[$field] = wp_json_encode($record[$field], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        $result = $wpdb->insert($table, $record);
        if ($result !== 1) {
            throw new RuntimeException('Unable to persist the application.');
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function decode(array $row): array
    {
        foreach (['redirect_uris', 'allowed_scopes'] as $field) {
            $decoded = json_decode(is_string($row[$field]) ? $row[$field] : '[]', true);
            $row[$field] = is_array($decoded) ? $decoded : [];
        }
        $row['trusted'] = (bool) $row['trusted'];
        $row['last_test_success'] = (bool) $row['last_test_success'];
        $row['owner_user_id'] = is_numeric($row['owner_user_id']) ? (int) $row['owner_user_id'] : 0;
        $row['version'] = is_numeric($row['version']) ? (int) $row['version'] : 0;

        return $row;
    }
}
