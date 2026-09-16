<?php

declare(strict_types=1);

namespace WpSsoProvider\Domain\Application;

enum ApplicationType: string
{
    case Web = 'web';
    case Spa = 'spa';
    case Native = 'native';
    case Desktop = 'desktop';
    case Machine = 'machine';
    case Saml = 'saml';
    case Cas = 'cas';
    case Scim = 'scim';
    case Custom = 'custom';
}
