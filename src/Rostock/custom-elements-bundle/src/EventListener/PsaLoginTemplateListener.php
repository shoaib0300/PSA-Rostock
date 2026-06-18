<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;

#[AsHook('parseTemplate')]
class PsaLoginTemplateListener
{
    public function __construct(private readonly PsaMemberFlash $flash)
    {
    }

    public function __invoke(Template $template): void
    {
        if ($template->getName() !== 'mod_login') {
            return;
        }

        $flash = $this->flash->consume();

        if ($flash === null) {
            return;
        }

        $template->psaFlashType = $flash['type'];
        $template->psaFlashMessage = $flash['message'];
    }
}
