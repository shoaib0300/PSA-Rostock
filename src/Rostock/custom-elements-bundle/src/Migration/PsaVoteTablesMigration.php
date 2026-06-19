<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaVoteTablesMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return !$schemaManager->tablesExist(['tl_psa_vote_reason'])
            || !$schemaManager->tablesExist(['tl_psa_vote_campaign'])
            || !$schemaManager->tablesExist(['tl_psa_vote_candidate'])
            || !$schemaManager->tablesExist(['tl_psa_vote_ballot']);
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_psa_vote_reason'])) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE tl_psa_vote_reason (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    tstamp int(10) unsigned NOT NULL DEFAULT 0,
                    sorting int(10) unsigned NOT NULL DEFAULT 0,
                    title varchar(255) NOT NULL DEFAULT '',
                    description text NULL,
                    photo binary(16) NULL,
                    published char(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (id),
                    KEY sorting (sorting),
                    KEY published (published)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
                SQL);
        }

        if (!$schemaManager->tablesExist(['tl_psa_vote_campaign'])) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE tl_psa_vote_campaign (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    tstamp int(10) unsigned NOT NULL DEFAULT 0,
                    title varchar(255) NOT NULL DEFAULT '',
                    description text NULL,
                    startDate int(10) unsigned NOT NULL DEFAULT 0,
                    endDate int(10) unsigned NOT NULL DEFAULT 0,
                    showResults varchar(16) NOT NULL DEFAULT 'after_vote',
                    published char(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (id),
                    KEY published (published),
                    KEY startDate (startDate),
                    KEY endDate (endDate)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
                SQL);
        }

        if (!$schemaManager->tablesExist(['tl_psa_vote_candidate'])) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE tl_psa_vote_candidate (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    pid int(10) unsigned NOT NULL DEFAULT 0,
                    tstamp int(10) unsigned NOT NULL DEFAULT 0,
                    sorting int(10) unsigned NOT NULL DEFAULT 0,
                    reason_id int(10) unsigned NOT NULL DEFAULT 0,
                    name varchar(255) NOT NULL DEFAULT '',
                    photo binary(16) NULL,
                    position varchar(255) NOT NULL DEFAULT '',
                    description text NULL,
                    published char(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (id),
                    KEY pid (pid),
                    KEY reason_id (reason_id),
                    KEY sorting (sorting)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
                SQL);
        }

        if (!$schemaManager->tablesExist(['tl_psa_vote_ballot'])) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE tl_psa_vote_ballot (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    tstamp int(10) unsigned NOT NULL DEFAULT 0,
                    campaign_id int(10) unsigned NOT NULL DEFAULT 0,
                    reason_id int(10) unsigned NOT NULL DEFAULT 0,
                    candidate_id int(10) unsigned NOT NULL DEFAULT 0,
                    member_id int(10) unsigned NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    UNIQUE KEY campaign_reason_member (campaign_id, reason_id, member_id),
                    KEY campaign_id (campaign_id),
                    KEY candidate_id (candidate_id),
                    KEY member_id (member_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
                SQL);
        }

        return $this->createResult(true, 'Created PSA vote tables.');
    }
}
