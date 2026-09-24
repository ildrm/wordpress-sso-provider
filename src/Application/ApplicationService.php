<?php

declare(strict_types=1);

namespace WpSsoProvider\Application;

use InvalidArgumentException;
use WpSsoProvider\Domain\Application\ApplicationType;
use WpSsoProvider\Protocol\OAuth\RedirectUriValidator;
use WpSsoProvider\Protocol\OAuth\ScopeSet;
use WpSsoProvider\Security\IdGenerator;
use WpSsoProvider\Security\TokenGenerator;

final class ApplicationService
{
    private const AVAILABLE_SCOPES = ['openid', 'profile', 'email', 'roles', 'groups'];

    public function __construct(
        private readonly ApplicationRepository $repository,
        private readonly RedirectUriValidator $redirects,
        private readonly TokenGenerator $tokens,
        private readonly IdGenerator $ids,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(int $siteId): array
    {
        return $this->repository->listForSite($siteId);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{application: array<string, mixed>, client_secret: string|null}
     */
    public function create(array $input, int $siteId, int $ownerUserId): array
    {
        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        if ($name === '' || mb_strlen($name) > 191) {
            throw new InvalidArgumentException('Application name must be between 1 and 191 characters.');
        }

        $type = ApplicationType::tryFrom(is_string($input['type'] ?? null) ? $input['type'] : '');
        if ($type === null) {
            throw new InvalidArgumentException('Unknown application type.');
        }

        $redirectUris = $input['redirect_uris'] ?? [];
        if (! is_array($redirectUris) || count($redirectUris) > 20) {
            throw new InvalidArgumentException('Redirect URIs must be an array with at most 20 entries.');
        }
        $normalizedUris = [];
        foreach ($redirectUris as $uri) {
            if (! is_string($uri)) {
                throw new InvalidArgumentException('Every redirect URI must be a string.');
            }
            $this->redirects->assertRegistrable($uri, $type);
            if (! in_array($uri, $normalizedUris, true)) {
                $normalizedUris[] = $uri;
            }
        }

        if (in_array($type, [ApplicationType::Web, ApplicationType::Spa, ApplicationType::Native, ApplicationType::Desktop], true) && $normalizedUris === []) {
            throw new InvalidArgumentException('Interactive applications require a redirect URI.');
        }

        $scopeInput = $input['allowed_scopes'] ?? ['openid', 'profile', 'email'];
        if (! is_array($scopeInput) || array_filter($scopeInput, 'is_string') !== $scopeInput) {
            throw new InvalidArgumentException('Allowed scopes must be an array of strings.');
        }
        $scopes = ScopeSet::fromRequest(implode(' ', $scopeInput), self::AVAILABLE_SCOPES)->all();

        $id = $this->ids->uuid();
        $clientId = 'wsp_' . $this->tokens->opaque(18);
        $isPublic = in_array($type, [ApplicationType::Spa, ApplicationType::Native, ApplicationType::Desktop], true);
        $secret = $isPublic ? null : $this->tokens->opaque(32);
        $now = gmdate('Y-m-d H:i:s');
        $record = [
            'id' => $id,
            'site_id' => $siteId,
            'client_id' => $clientId,
            'client_secret_hash' => $secret === null ? null : password_hash($secret, PASSWORD_DEFAULT),
            'name' => $name,
            'type' => $type->value,
            'status' => 'draft',
            'trusted' => 0,
            'redirect_uris' => $normalizedUris,
            'allowed_scopes' => $scopes,
            'settings' => [],
            'owner_user_id' => $ownerUserId,
            'last_tested_at' => null,
            'last_test_success' => 0,
            'version' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->repository->insert($record);
        unset($record['client_secret_hash']);

        return ['application' => $record, 'client_secret' => $secret];
    }
}
