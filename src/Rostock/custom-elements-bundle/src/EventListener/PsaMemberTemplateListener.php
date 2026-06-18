<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;

#[AsHook('parseTemplate')]
class PsaMemberTemplateListener
{
    public function __invoke(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'member_')) {
            return;
        }

        $template->hideHeadline = true;
        $template->profileHint = $GLOBALS['TL_LANG']['tl_member']['profileHint'] ?? '';
        $template->profileDetails = $GLOBALS['TL_LANG']['tl_member']['profileDetails'] ?? '';
    }
}
