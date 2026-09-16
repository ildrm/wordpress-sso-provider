<?php

global $wpdb;

$hash = hash('sha256', 'concurrent-authorization-code-fixture');
$table = $wpdb->prefix . 'sso_auth_codes';
$wpdb->delete($table, ['code_hash' => $hash]);
$inserted = $wpdb->insert($table, [
    'code_hash' => $hash,
    'site_id' => get_current_blog_id(),
    'application_id' => '00000000-0000-4000-8000-000000000001',
    'user_id' => 1,
    'session_id_hash' => str_repeat('c', 64),
    'redirect_uri' => 'https://client.example.test/concurrent',
    'scopes' => '["openid"]',
    'nonce' => null,
    'pkce_challenge' => str_repeat('d', 43),
    'expires_at' => gmdate('Y-m-d H:i:s', time() + 300),
    'consumed_at' => null,
    'created_at' => gmdate('Y-m-d H:i:s'),
]);

if ($inserted !== 1) {
    throw new RuntimeException('Unable to seed concurrency fixture.');
}

