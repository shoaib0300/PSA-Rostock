<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\MemberModel;

final class PsaMemberAccess
{
    public static function isLoginAllowed(MemberModel $member): bool
    {
        if ($member->disable) {
            return false;
        }

        if (!$member->login) {
            return false;
        }

        $now = time();
        $start = (int) $member->start;
        $stop = (int) $member->stop;

        if ($start > 0 && $start > $now) {
            return false;
        }

        if ($stop > 0 && $stop <= $now) {
            return false;
        }

        return true;
    }

    public static function findByLoginIdentifier(string $identifier): ?MemberModel
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $member = MemberModel::findOneBy('username', $identifier);

        if ($member !== null) {
            return $member;
        }

        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return MemberModel::findOneBy('email', $identifier);
    }
}
