<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\MemberModel;
use Contao\ModuleRegistration;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;

#[AsHook('createNewUser')]
class PsaMemberRegistrationListener
{
    public function __construct(private readonly PsaMemberFlash $flash)
    {
    }

    public function __invoke(int $id, array $arrData, ModuleRegistration $module): void
    {
        if ($module->reg_activate) {
            $this->flash->set(PsaMemberFlash::TYPE_REGISTRATION_PENDING);

            return;
        }

        $member = MemberModel::findById($id);

        if ($member === null) {
            return;
        }

        $member->disable = false;
        $member->login = true;
        $member->save();

        $this->preventInvalidAdminNotification();
    }

    private function preventInvalidAdminNotification(): void
    {
        $adminEmail = trim((string) ($GLOBALS['TL_ADMIN_EMAIL'] ?? ''));

        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            unset($GLOBALS['TL_ADMIN_EMAIL'], $GLOBALS['TL_ADMIN_NAME']);
        }
    }
}
