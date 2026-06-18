<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\MemberModel;
use Contao\ModuleLostPassword;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;

#[AsHook('setNewPassword')]
class PsaMemberPasswordListener
{
    public function __construct(private readonly PsaMemberFlash $flash)
    {
    }

    public function __invoke(MemberModel $member, string $password, ModuleLostPassword $module): void
    {
        $this->flash->set(PsaMemberFlash::TYPE_PASSWORD_CHANGED);
    }
}
