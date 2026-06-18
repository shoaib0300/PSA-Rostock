<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupPollVoteTableMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup'])) {
            return false;
        }

        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup_poll_vote']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_meetup_poll_vote (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                meetup_id int(10) unsigned NOT NULL DEFAULT 0,
                option_id int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY meetup_member (meetup_id, member_id),
                KEY meetup_id (meetup_id),
                KEY option_id (option_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        return $this->createResult(true, 'Created tl_psa_meetup_poll_vote table.');
    }
}
