<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Security;

use Contao\CoreBundle\Security\User\ContaoUserProvider;
use Contao\MemberModel;
use Contao\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * Allows front end login with username or e-mail address.
 */
final class PsaFrontendUserProvider extends ContaoUserProvider
{
    public function loadUserByIdentifier(string $identifier): User
    {
        try {
            return parent::loadUserByIdentifier($identifier);
        } catch (UserNotFoundException) {
            // Continue with e-mail lookup below.
        }

        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            throw new UserNotFoundException(\sprintf('Could not find user "%s"', $identifier));
        }

        $member = MemberModel::findOneBy('email', $identifier);

        if ($member === null || !\is_string($member->username) || $member->username === '') {
            throw new UserNotFoundException(\sprintf('Could not find user "%s"', $identifier));
        }

        return parent::loadUserByIdentifier($member->username);
    }
}
