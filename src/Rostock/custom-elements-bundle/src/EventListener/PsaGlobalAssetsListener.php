<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('generatePage')]
class PsaGlobalAssetsListener
{
    public function __invoke(): void
    {
        $GLOBALS['TL_CSS']['psa_fonts'] = 'bundles/customelements/frontend/css/psa_fonts.css';
        $GLOBALS['TL_CSS']['psa_main'] = 'files/tpl/css/main.css';
        $GLOBALS['TL_CSS']['psa_events'] = 'bundles/customelements/frontend/css/psa_events.css';
    }
}
