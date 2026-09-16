<?php

declare(strict_types=1);

namespace WpSsoProvider\Tests\Unit;

use DomainException;
use PHPUnit\Framework\TestCase;
use WpSsoProvider\Domain\Application\Application;
use WpSsoProvider\Domain\Application\ApplicationStatus;
use WpSsoProvider\Domain\Application\ApplicationType;

final class ApplicationTest extends TestCase
{
    public function test_new_application_is_draft_and_cannot_activate(): void
    {
        $application = Application::draft('app_01', 'Payroll', ApplicationType::Web);

        self::assertSame(ApplicationStatus::Draft, $application->status());
        $this->expectException(DomainException::class);
        $application->activate();
    }

    public function test_only_successfully_tested_application_activates(): void
    {
        $application = Application::draft('app_01', 'Payroll', ApplicationType::Web);
        $application->configure();
        $application->recordSuccessfulTest();
        $application->activate();

        self::assertSame(ApplicationStatus::Active, $application->status());
        self::assertNotNull($application->lastTestedAt());
    }

    public function test_failed_test_does_not_unlock_activation(): void
    {
        $application = Application::draft('app_01', 'Payroll', ApplicationType::Web);
        $application->configure();
        $application->recordFailedTest();

        $this->expectException(DomainException::class);
        $application->activate();
    }
}
