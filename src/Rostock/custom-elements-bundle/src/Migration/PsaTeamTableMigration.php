<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaTeamTableMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_team_member']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_team_member (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                sorting int(10) unsigned NOT NULL DEFAULT 0,
                name varchar(255) NOT NULL DEFAULT '',
                email varchar(255) NOT NULL DEFAULT '',
                phone varchar(64) NOT NULL DEFAULT '',
                position varchar(255) NOT NULL DEFAULT '',
                degree varchar(255) NOT NULL DEFAULT '',
                university varchar(255) NOT NULL DEFAULT '',
                photo binary(16) DEFAULT NULL,
                published char(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (id),
                KEY sorting (sorting),
                KEY published (published)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        return $this->createResult(true, 'Created table tl_psa_team_member.');
    }
}
