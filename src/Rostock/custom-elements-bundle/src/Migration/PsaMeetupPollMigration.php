<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupPollMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup'])) {
            return false;
        }

        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_psa_meetup');

        return !isset($columns['posttype'])
            || !$schemaManager->tablesExist(['tl_psa_meetup_poll_option'])
            || !$schemaManager->tablesExist(['tl_psa_meetup_poll_vote']);
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_psa_meetup');

        if (!isset($columns['posttype'])) {
            $this->connection->executeStatement("ALTER TABLE tl_psa_meetup ADD postType varchar(16) NOT NULL DEFAULT 'meetup' AFTER location");
            $this->connection->executeStatement("ALTER TABLE tl_psa_meetup ADD pollQuestion varchar(255) NOT NULL DEFAULT '' AFTER postType");
        }

        if (!$schemaManager->tablesExist(['tl_psa_meetup_poll_option'])) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE tl_psa_meetup_poll_option (
                    id int(10) unsigned NOT NULL AUTO_INCREMENT,
                    pid int(10) unsigned NOT NULL DEFAULT 0,
                    label varchar(255) NOT NULL DEFAULT '',
                    sorting int(10) unsigned NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    KEY pid (pid)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
                SQL);
        }

        if (!$schemaManager->tablesExist(['tl_psa_meetup_poll_vote'])) {
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
        }

        return $this->createResult(true, 'Added meetup post types and poll tables.');
    }
}
