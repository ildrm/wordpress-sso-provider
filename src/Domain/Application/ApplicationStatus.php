<?php

declare(strict_types=1);

namespace WpSsoProvider\Domain\Application;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Configured = 'configured';
    case Tested = 'tested';
    case Active = 'active';
    case Disabled = 'disabled';
}
