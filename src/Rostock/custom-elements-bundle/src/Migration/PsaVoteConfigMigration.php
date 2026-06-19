<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaVoteConfigMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_config']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_vote_config (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                showTicker char(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_vote_config (id, tstamp, showTicker) VALUES (?, ?, ?)',
            [1, time(), '1'],
        );

        return $this->createResult(true, 'Created tl_psa_vote_config with default settings.');
    }
}
