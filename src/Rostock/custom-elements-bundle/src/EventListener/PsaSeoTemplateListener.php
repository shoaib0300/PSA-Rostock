<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\PageModel;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaSeo;

#[AsHook('parseTemplate')]
class PsaSeoTemplateListener
{
    public function __invoke(Template $template): void
    {
        if ($template->getName() !== 'fe_page') {
            return;
        }

        $page = PageModel::findByPk($GLOBALS['objPage']->id ?? 0);

        $template->psaSeo = PsaSeo::buildForPage(
            $page,
            (string) ($template->title ?? ''),
            (string) ($template->description ?? ''),
            $template->canonical ?: null,
        );
    }
}
