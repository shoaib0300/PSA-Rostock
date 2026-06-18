<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupTablesMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_meetup (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                title varchar(255) NOT NULL DEFAULT '',
                description text NULL,
                meetupDate int(10) unsigned NOT NULL DEFAULT 0,
                location varchar(255) NOT NULL DEFAULT '',
                published char(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (id),
                KEY member_id (member_id),
                KEY meetupDate (meetupDate),
                KEY published (published)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_meetup_join (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                pid int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY meetup_member (pid, member_id),
                KEY pid (pid),
                KEY member_id (member_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_meetup_comment (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                pid int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                comment text NULL,
                published char(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (id),
                KEY pid (pid),
                KEY member_id (member_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        return $this->createResult(true, 'Created meetup tables (tl_psa_meetup, tl_psa_meetup_join, tl_psa_meetup_comment).');
    }
}
