<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Security;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\User\UserChecker as ContaoUserChecker;
use Contao\FrontendUser;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Blocks deactivated frontend members with a clear login error.
 */
final class PsaUserChecker extends ContaoUserChecker
{
    public function __construct(ContaoFramework $framework)
    {
        parent::__construct($framework);
    }

    public function checkPreAuth(UserInterface $user): void
    {
        try {
            parent::checkPreAuth($user);
        } catch (DisabledException $exception) {
            if (!$user instanceof FrontendUser) {
                throw $exception;
            }

            $deactivated = new DisabledException('account_deactivated');
            $deactivated->setUser($user);

            throw $deactivated;
        }
    }
}
