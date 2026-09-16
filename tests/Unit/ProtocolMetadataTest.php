<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WpSsoProvider\Protocol\Oidc\DiscoveryMetadata;
use WpSsoProvider\Protocol\Scim\FilterParser;

final class ProtocolMetadataTest extends TestCase
{
    public function test_discovery_advertises_only_supported_secure_capabilities(): void
    {
        $metadata = (new DiscoveryMetadata('https://idp.example/sso'))->toArray();

        self::assertSame(['code'], $metadata['response_types_supported']);
        self::assertSame(['S256'], $metadata['code_challenge_methods_supported']);
        self::assertNotContains('none', $metadata['id_token_signing_alg_values_supported']);
        self::assertArrayNotHasKey('registration_endpoint', $metadata);
    }

    public function test_scim_filter_parser_accepts_safe_subset_and_rejects_injection(): void
    {
        $filter = (new FilterParser())->parse('userName eq "bjensen"');
        self::assertSame('userName', $filter->attribute);
        self::assertSame('eq', $filter->operator);
        self::assertSame('bjensen', $filter->value);

        $this->expectException(\InvalidArgumentException::class);
        (new FilterParser())->parse('userName eq "x" or 1 eq 1');
    }
}
