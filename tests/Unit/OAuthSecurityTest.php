<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WpSsoProvider\Domain\Application\ApplicationType;
use WpSsoProvider\Protocol\OAuth\PkceVerifier;
use WpSsoProvider\Protocol\OAuth\RedirectUriValidator;
use WpSsoProvider\Protocol\OAuth\ScopeSet;

final class OAuthSecurityTest extends TestCase
{
    public function test_redirect_uri_is_an_exact_octet_match(): void
    {
        $validator = new RedirectUriValidator();
        self::assertTrue($validator->matches('https://client.example/cb', ['https://client.example/cb']));
        self::assertFalse($validator->matches('https://client.example/cb/', ['https://client.example/cb']));
        self::assertFalse($validator->matches('https://CLIENT.example/cb', ['https://client.example/cb']));
        self::assertFalse($validator->matches('https://client.example/cb?next=1', ['https://client.example/cb']));
    }

    #[DataProvider('invalidRedirects')]
    public function test_unsafe_redirects_are_not_registrable(string $uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RedirectUriValidator())->assertRegistrable($uri, ApplicationType::Web);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidRedirects(): iterable
    {
        yield 'fragment' => ['https://client.example/cb#fragment'];
        yield 'userinfo' => ['https://user:pass@client.example/cb'];
        yield 'wildcard' => ['https://*.example/cb'];
        yield 'http remote' => ['http://client.example/cb'];
        yield 'malformed' => ['not a URI'];
        yield 'localhost loopback' => ['http://localhost:38111/cb'];
        yield 'encoded newline' => ['https://client.example/cb%0a'];
        yield 'bad percent encoding' => ['https://client.example/cb%zz'];
        yield 'unicode host' => ['https://exämple.com/cb'];
        yield 'host with trailing dot' => ['https://client.example./cb'];
    }

    public function test_native_redirect_categories_and_dynamic_loopback_port(): void
    {
        $validator = new RedirectUriValidator();
        $registered = [
            'https://app.example.com/oauth/callback',
            'com.example.app:/oauth2redirect/provider',
            'http://127.0.0.1:10000/oauth/callback?source=app',
            'http://[::1]:10000/oauth/callback',
        ];

        foreach ($registered as $uri) {
            $validator->assertRegistrable($uri, ApplicationType::Native);
        }
        self::assertTrue($validator->matches('com.example.app:/oauth2redirect/provider', $registered, ApplicationType::Native));
        self::assertTrue($validator->matches('http://127.0.0.1:61234/oauth/callback?source=app', $registered, ApplicationType::Native));
        self::assertTrue($validator->matches('http://[::1]:61234/oauth/callback', $registered, ApplicationType::Native));
        self::assertFalse($validator->matches('http://127.0.0.1:61234/oauth/other?source=app', $registered, ApplicationType::Native));
        self::assertFalse($validator->matches('http://127.0.0.1:61234/oauth/callback?source=other', $registered, ApplicationType::Native));
        self::assertFalse($validator->matches('http://localhost:61234/oauth/callback?source=app', $registered, ApplicationType::Native));
        self::assertFalse($validator->matches('http://[::1]:61234/oauth/callback?source=app', $registered, ApplicationType::Native));
    }

    #[DataProvider('invalidNativeRedirects')]
    public function test_unsafe_native_redirects_are_rejected(string $uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RedirectUriValidator())->assertRegistrable($uri, ApplicationType::Native);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidNativeRedirects(): iterable
    {
        yield 'generic scheme' => ['myapp:/callback'];
        yield 'single-dot scheme' => ['example.app:/callback'];
        yield 'invalid reverse-domain label' => ['com.example-.app:/callback'];
        yield 'private scheme authority' => ['com.example.app://attacker/callback'];
        yield 'remote HTTP' => ['http://app.example.com/callback'];
        yield 'localhost' => ['http://localhost:8888/callback'];
        yield 'wrong loopback address' => ['http://127.1:8888/callback'];
        yield 'zero port' => ['http://127.0.0.1:0/callback'];
        yield 'fragment' => ['com.example.app:/callback#fragment'];
    }

    public function test_s256_pkce_vector_from_rfc_7636(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        self::assertTrue((new PkceVerifier())->verify($verifier, $challenge, 'S256'));
        self::assertFalse((new PkceVerifier())->verify($verifier . 'x', $challenge, 'S256'));
    }

    public function test_plain_pkce_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PkceVerifier())->verify(str_repeat('a', 43), str_repeat('a', 43), 'plain');
    }

    public function test_scope_set_rejects_unknown_or_prohibited_scopes(): void
    {
        $scopes = ScopeSet::fromRequest('openid profile email', ['openid', 'profile', 'email']);
        self::assertSame(['openid', 'profile', 'email'], $scopes->all());

        $this->expectException(InvalidArgumentException::class);
        ScopeSet::fromRequest('openid admin', ['openid', 'profile']);
    }
}
