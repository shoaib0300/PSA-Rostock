<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('initializeSystem')]
class PsaBackendBrandingListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public function __invoke(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->scopeMatcher->isBackendRequest($request)) {
            return;
        }

        $GLOBALS['TL_CSS']['psa_backend_branding'] = 'bundles/customelements/backend/css/psa_backend.css?v=1';
    }
}
