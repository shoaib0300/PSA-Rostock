<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupJoinStatusMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup_join'])) {
            return false;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns('tl_psa_meetup_join');

        return !isset($columns['status']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            "ALTER TABLE tl_psa_meetup_join ADD status varchar(16) NOT NULL DEFAULT 'join' AFTER member_id"
        );

        return $this->createResult(true, 'Added join status column to tl_psa_meetup_join.');
    }
}
