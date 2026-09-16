<?php

wp_set_current_user(1);

$statusRequest = new WP_REST_Request('GET', '/wp-sso-provider/v1/status');
$statusResponse = rest_do_request($statusRequest);
if ($statusResponse->get_status() !== 200) {
    throw new RuntimeException('Status endpoint did not return HTTP 200.');
}
$status = $statusResponse->get_data();
if (($status['protocols']['oidc'] ?? null) !== 'experimental_not_exposed') {
    throw new RuntimeException('Protocol status must not overstate OIDC support.');
}

$createRequest = new WP_REST_Request('POST', '/wp-sso-provider/v1/applications');
$createRequest->set_header('Content-Type', 'application/json');
$createRequest->set_body(wp_json_encode([
    'name' => 'Integration Test Client',
    'type' => 'web',
    'redirect_uris' => ['https://client.example.test/callback'],
    'allowed_scopes' => ['openid', 'profile', 'email'],
]));
$createResponse = rest_do_request($createRequest);
if ($createResponse->get_status() !== 201) {
    throw new RuntimeException('Application endpoint did not return HTTP 201: ' . wp_json_encode($createResponse->get_data()));
}
$created = $createResponse->get_data();
$secret = $created['client_secret'] ?? null;
if (! is_string($secret) || strlen($secret) < 43) {
    throw new RuntimeException('Confidential client secret was not returned exactly once.');
}

global $wpdb;
$table = $wpdb->prefix . 'sso_applications';
$stored = $wpdb->get_row(
    $wpdb->prepare("SELECT status, client_secret_hash FROM {$table} WHERE id = %s", $created['application']['id']),
    ARRAY_A
);
if (! is_array($stored) || $stored['status'] !== 'draft') {
    throw new RuntimeException('Application did not persist as draft.');
}
if (! password_verify($secret, $stored['client_secret_hash']) || str_contains($stored['client_secret_hash'], $secret)) {
    throw new RuntimeException('Client secret was not irreversibly stored.');
}

$invalidRequest = new WP_REST_Request('POST', '/wp-sso-provider/v1/applications');
$invalidRequest->set_header('Content-Type', 'application/json');
$invalidRequest->set_body(wp_json_encode([
    'name' => 'Unsafe Redirect Client',
    'type' => 'web',
    'redirect_uris' => ['https://*.example.test/callback'],
]));
$invalidResponse = rest_do_request($invalidRequest);
if ($invalidResponse->get_status() !== 400) {
    throw new RuntimeException('Unsafe redirect registration was not rejected.');
}

wp_set_current_user(0);
$unauthorizedResponse = rest_do_request(new WP_REST_Request('GET', '/wp-sso-provider/v1/applications'));
if ($unauthorizedResponse->get_status() !== 401) {
    throw new RuntimeException('Unauthenticated application listing was not rejected.');
}

$oneTimeStore = new WpSsoProvider\Infrastructure\WordPress\DatabaseOneTimeCredentialStore();
$codeHash = hash('sha256', 'integration-authorization-code-' . wp_generate_uuid4());
$now = gmdate('Y-m-d H:i:s');
$future = gmdate('Y-m-d H:i:s', time() + 300);
$wpdb->insert($wpdb->prefix . 'sso_auth_codes', [
    'code_hash' => $codeHash,
    'site_id' => get_current_blog_id(),
    'application_id' => $created['application']['id'],
    'user_id' => 1,
    'session_id_hash' => str_repeat('a', 64),
    'redirect_uri' => 'https://client.example.test/callback',
    'scopes' => '["openid"]',
    'nonce' => 'nonce-1',
    'pkce_challenge' => str_repeat('b', 43),
    'expires_at' => $future,
    'consumed_at' => null,
    'created_at' => $now,
]);
$firstCode = $oneTimeStore->consumeAuthorizationCode(
    get_current_blog_id(),
    $codeHash,
    $created['application']['id'],
    'https://client.example.test/callback',
    $now
);
$secondCode = $oneTimeStore->consumeAuthorizationCode(
    get_current_blog_id(),
    $codeHash,
    $created['application']['id'],
    'https://client.example.test/callback',
    $now
);
if (! is_array($firstCode) || $secondCode !== null) {
    throw new RuntimeException('Authorization code was not consumed exactly once.');
}

$ticketHash = hash('sha256', 'ST-integration-ticket-' . wp_generate_uuid4());
$wpdb->insert($wpdb->prefix . 'sso_cas_tickets', [
    'ticket_hash' => $ticketHash,
    'site_id' => get_current_blog_id(),
    'application_id' => $created['application']['id'],
    'user_id' => 1,
    'service_uri' => 'https://service.example.test/login',
    'expires_at' => $future,
    'consumed_at' => null,
    'created_at' => $now,
]);
if ($oneTimeStore->consumeCasTicket(get_current_blog_id(), $ticketHash, 'https://wrong.example.test', $now)) {
    throw new RuntimeException('CAS ticket was accepted for the wrong service.');
}
if (! $oneTimeStore->consumeCasTicket(get_current_blog_id(), $ticketHash, 'https://service.example.test/login', $now)) {
    throw new RuntimeException('CAS ticket was not accepted for its registered service.');
}
if ($oneTimeStore->consumeCasTicket(get_current_blog_id(), $ticketHash, 'https://service.example.test/login', $now)) {
    throw new RuntimeException('CAS ticket was consumed more than once.');
}

echo "WordPress REST and persistence integration tests passed.\n";
