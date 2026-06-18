<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaEventRsvpTableEnsureMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_event_rsvp']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_event_rsvp (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                event_id int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                status varchar(8) COLLATE ascii_bin NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                UNIQUE KEY event_member (event_id, member_id),
                KEY event_id (event_id),
                KEY member_id (member_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        return $this->createResult(true, 'Recreated table tl_psa_event_rsvp.');
    }
}
