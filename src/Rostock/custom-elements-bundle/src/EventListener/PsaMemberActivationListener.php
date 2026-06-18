<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\MemberModel;
use Contao\ModuleRegistration;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;

#[AsHook('activateAccount')]
class PsaMemberActivationListener
{
    public function __construct(private readonly PsaMemberFlash $flash)
    {
    }

    public function __invoke(MemberModel $member, ModuleRegistration $module): void
    {
        $this->flash->set(PsaMemberFlash::TYPE_ACCOUNT_ACTIVATED);
    }
}
