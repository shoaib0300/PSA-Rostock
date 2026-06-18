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
        $GLOBALS['TL_CSS']['psa_button'] = 'bundles/customelements/frontend/css/psa_button.css';
        $GLOBALS['TL_CSS']['psa_events'] = 'bundles/customelements/frontend/css/psa_events.css';
        $GLOBALS['TL_CSS']['psa_meetups'] = 'bundles/customelements/frontend/css/psa_meetups.css';
        $GLOBALS['TL_BODY']['psa_events'] = \Contao\FrontendTemplate::generateScriptTag('bundles/customelements/frontend/js/psa_events.js');
        $GLOBALS['TL_BODY']['psa_meetups'] = \Contao\FrontendTemplate::generateScriptTag('bundles/customelements/frontend/js/psa_meetups.js');
    }
}
