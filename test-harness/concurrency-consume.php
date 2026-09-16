<?php

$store = new WpSsoProvider\Infrastructure\WordPress\DatabaseOneTimeCredentialStore();
$result = $store->consumeAuthorizationCode(
    get_current_blog_id(),
    hash('sha256', 'concurrent-authorization-code-fixture'),
    '00000000-0000-4000-8000-000000000001',
    'https://client.example.test/concurrent',
    gmdate('Y-m-d H:i:s')
);

echo is_array($result) ? "CONSUMED\n" : "REJECTED\n";

