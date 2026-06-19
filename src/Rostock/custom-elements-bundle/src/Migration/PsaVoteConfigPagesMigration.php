<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaVoteConfigPagesMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_config'])) {
            return false;
        }

        foreach ($this->connection->createSchemaManager()->listTableColumns('tl_psa_vote_config') as $column) {
            if (strcasecmp($column->getName(), 'tickerPages') === 0) {
                return false;
            }
        }

        return true;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            'ALTER TABLE tl_psa_vote_config ADD tickerPages blob NULL',
        );

        return $this->createResult(true, 'Added tl_psa_vote_config.tickerPages for page-specific ticker display.');
    }
}
