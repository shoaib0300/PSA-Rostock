<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Module;

use Contao\ModuleRegistration;

/**
 * Skips the admin notification when no valid admin e-mail is configured.
 */
class PsaModuleRegistration extends ModuleRegistration
{
    protected function sendAdminNotification($intId, $arrData): void
    {
        $adminEmail = trim((string) ($GLOBALS['TL_ADMIN_EMAIL'] ?? ''));

        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        parent::sendAdminNotification($intId, $arrData);
    }
}
