<?php

declare(strict_types=1);

namespace WpSsoProvider\Application;

interface ApplicationRepository
{
    /** @return list<array<string, mixed>> */
    public function listForSite(int $siteId): array;

    /** @param array<string, mixed> $record */
    public function insert(array $record): void;
}
