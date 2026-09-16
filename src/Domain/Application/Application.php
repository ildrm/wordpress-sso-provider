<?php

declare(strict_types=1);

namespace WpSsoProvider\Domain\Application;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class Application
{
    private ApplicationStatus $status;
    private ?DateTimeImmutable $lastTestedAt = null;
    private bool $lastTestSuccessful = false;

    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly ApplicationType $type,
    ) {
        if ($id === '' || $name === '') {
            throw new InvalidArgumentException('Application ID and name are required.');
        }

        $this->status = ApplicationStatus::Draft;
    }

    public static function draft(string $id, string $name, ApplicationType $type): self
    {
        return new self($id, $name, $type);
    }

    public function configure(): void
    {
        if (! in_array($this->status, [ApplicationStatus::Draft, ApplicationStatus::Disabled], true)) {
            throw new DomainException('Only draft or disabled applications can be configured.');
        }

        $this->status = ApplicationStatus::Configured;
        $this->lastTestSuccessful = false;
    }

    public function recordSuccessfulTest(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== ApplicationStatus::Configured) {
            throw new DomainException('Application must be configured before testing.');
        }

        $this->lastTestedAt = $at ?? new DateTimeImmutable('now');
        $this->lastTestSuccessful = true;
        $this->status = ApplicationStatus::Tested;
    }

    public function recordFailedTest(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== ApplicationStatus::Configured) {
            throw new DomainException('Application must be configured before testing.');
        }

        $this->lastTestedAt = $at ?? new DateTimeImmutable('now');
        $this->lastTestSuccessful = false;
    }

    public function activate(): void
    {
        if ($this->status !== ApplicationStatus::Tested || ! $this->lastTestSuccessful) {
            throw new DomainException('A successful connection test is required before activation.');
        }

        $this->status = ApplicationStatus::Active;
    }

    public function disable(): void
    {
        if ($this->status === ApplicationStatus::Draft) {
            throw new DomainException('A draft application cannot be disabled.');
        }

        $this->status = ApplicationStatus::Disabled;
    }

    public function status(): ApplicationStatus
    {
        return $this->status;
    }

    public function lastTestedAt(): ?DateTimeImmutable
    {
        return $this->lastTestedAt;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): ApplicationType
    {
        return $this->type;
    }
}
