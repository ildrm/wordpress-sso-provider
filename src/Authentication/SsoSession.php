<?php

declare(strict_types=1);

namespace WpSsoProvider\Authentication;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class SsoSession
{
    private DateTimeImmutable $lastActivityAt;
    private ?DateTimeImmutable $revokedAt = null;

    /** @param list<string> $methods */
    private function __construct(
        private readonly string $id,
        private readonly int $userId,
        private readonly DateTimeImmutable $createdAt,
        private readonly DateTimeImmutable $authenticationTime,
        private array $methods,
        private int $strength,
        private readonly int $idleTimeout,
        private readonly int $absoluteTimeout,
    ) {
        $this->lastActivityAt = $createdAt;
    }

    /** @param list<string> $methods */
    public static function start(
        string $id,
        int $userId,
        DateTimeImmutable $at,
        array $methods,
        int $strength,
        int $idleTimeout,
        int $absoluteTimeout,
    ): self {
        if ($id === '' || $userId <= 0 || $methods === [] || $strength < 1 || $idleTimeout < 1 || $absoluteTimeout < $idleTimeout) {
            throw new InvalidArgumentException('Invalid SSO session parameters.');
        }

        return new self($id, $userId, $at, $at, array_values(array_unique($methods)), $strength, $idleTimeout, $absoluteTimeout);
    }

    public function isValidAt(DateTimeImmutable $at): bool
    {
        if ($at < $this->createdAt) {
            return false;
        }

        if ($this->revokedAt !== null && $at >= $this->revokedAt) {
            return false;
        }

        return $at->getTimestamp() < $this->lastActivityAt->getTimestamp() + $this->idleTimeout
            && $at->getTimestamp() < $this->createdAt->getTimestamp() + $this->absoluteTimeout;
    }

    public function touch(DateTimeImmutable $at): void
    {
        if (! $this->isValidAt($at) || $at < $this->lastActivityAt) {
            throw new DomainException('Cannot touch an invalid session.');
        }

        $this->lastActivityAt = $at;
    }

    public function stepUp(string $method, int $strength, DateTimeImmutable $at): void
    {
        if (! $this->isValidAt($at) || $strength <= $this->strength || $method === '') {
            throw new DomainException('Invalid step-up result.');
        }

        if (! in_array($method, $this->methods, true)) {
            $this->methods[] = $method;
        }
        $this->strength = $strength;
        $this->lastActivityAt = $at;
    }

    public function revoke(DateTimeImmutable $at): void
    {
        if ($this->revokedAt === null || $at < $this->revokedAt) {
            $this->revokedAt = $at;
        }
    }

    /** @return list<string> */
    public function methods(): array
    {
        return $this->methods;
    }

    public function strength(): int
    {
        return $this->strength;
    }

    public function authenticationTime(): DateTimeImmutable
    {
        return $this->authenticationTime;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
