<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMemberGenderFieldMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_member'])) {
            return false;
        }

        return !$this->connection->createSchemaManager()->introspectTable('tl_member')->hasColumn('gender');
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            "ALTER TABLE tl_member ADD gender varchar(24) NOT NULL DEFAULT ''",
        );

        return $this->createResult(true, 'Added gender column to tl_member.');
    }
}
