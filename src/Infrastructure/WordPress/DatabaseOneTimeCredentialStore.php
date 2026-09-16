<?php

declare(strict_types=1);

namespace WpSsoProvider\Infrastructure\WordPress;

use RuntimeException;
use wpdb;

final class DatabaseOneTimeCredentialStore
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

    /** @return array<string, mixed>|null */
    public function consumeAuthorizationCode(
        int $siteId,
        string $codeHash,
        string $applicationId,
        string $redirectUri,
        string $now,
    ): ?array {
        $wpdb = $this->database;
        $table = $wpdb->prefix . 'sso_auth_codes';
        $query = $wpdb->prepare(
            'UPDATE %i SET consumed_at = %s
                WHERE code_hash = %s AND site_id = %d AND application_id = %s
                AND redirect_uri = %s AND consumed_at IS NULL AND expires_at > %s',
            $table,
            $now,
            $codeHash,
            $siteId,
            $applicationId,
            $redirectUri,
            $now
        );
        if (! is_string($query)) {
            throw new RuntimeException('Unable to prepare authorization-code consumption.');
        }
        $updated = $wpdb->query($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared and type-checked immediately above.

        if ($updated !== 1) {
            return null;
        }

        $select = $wpdb->prepare(
            'SELECT application_id, user_id, session_id_hash, redirect_uri, scopes, nonce, pkce_challenge, expires_at
                FROM %i WHERE code_hash = %s AND site_id = %d',
            $table,
            $codeHash,
            $siteId
        );
        if (! is_string($select)) {
            throw new RuntimeException('Unable to prepare authorization-code lookup.');
        }
        $row = $wpdb->get_row($select, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared and type-checked immediately above.

        if (! is_array($row)) {
            return null;
        }

        $normalized = [];
        foreach ($row as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    public function consumeCasTicket(int $siteId, string $ticketHash, string $serviceUri, string $now): bool
    {
        $wpdb = $this->database;
        $table = $wpdb->prefix . 'sso_cas_tickets';
        $query = $wpdb->prepare(
            'UPDATE %i SET consumed_at = %s
                WHERE ticket_hash = %s AND site_id = %d AND service_uri = %s
                AND consumed_at IS NULL AND expires_at > %s',
            $table,
            $now,
            $ticketHash,
            $siteId,
            $serviceUri,
            $now
        );
        if (! is_string($query)) {
            throw new RuntimeException('Unable to prepare CAS ticket consumption.');
        }
        $updated = $wpdb->query($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared and type-checked immediately above.

        return $updated === 1;
    }
}
