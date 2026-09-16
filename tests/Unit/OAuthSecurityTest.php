<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
        (new RedirectUriValidator())->assertRegistrable($uri, false);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidRedirects(): iterable
    {
        yield 'fragment' => ['https://client.example/cb#fragment'];
        yield 'userinfo' => ['https://user:pass@client.example/cb'];
        yield 'wildcard' => ['https://*.example/cb'];
        yield 'http remote' => ['http://client.example/cb'];
        yield 'malformed' => ['not a URI'];
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
