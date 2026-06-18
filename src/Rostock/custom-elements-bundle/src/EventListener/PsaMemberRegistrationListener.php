<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\MemberModel;
use Contao\ModuleRegistration;

#[AsHook('createNewUser')]
class PsaMemberRegistrationListener
{
    public function __invoke(int $id, array $arrData, ModuleRegistration $module): void
    {
        if ($module->reg_activate) {
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
