<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\System;
use Doctrine\DBAL\Connection;

final class PsaMemberStats
{
    /**
     * @return array{active: int, inRostock: int}
     */
    public static function resolve(): array
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        $now = time();

        $active = (int) $connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(*)
                FROM tl_member
                WHERE login = '1'
                  AND disable = '0'
                  AND (start = '' OR start <= ?)
                  AND (stop = '' OR stop > ?)
                SQL,
            [$now, $now],
        );

        $inRostock = (int) $connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(*)
                FROM tl_member
                WHERE login = '1'
                  AND disable = '0'
                  AND (start = '' OR start <= ?)
                  AND (stop = '' OR stop > ?)
                  AND cityGermany LIKE ?
                SQL,
            [$now, $now, '%Rostock%'],
        );

        return [
            'active' => $active,
            'inRostock' => $inRostock,
        ];
    }
}
