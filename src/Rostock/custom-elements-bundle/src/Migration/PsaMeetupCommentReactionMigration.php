<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupCommentReactionMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return !$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup_comment_reaction']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE tl_psa_meetup_comment_reaction (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                tstamp int(10) unsigned NOT NULL DEFAULT 0,
                comment_id int(10) unsigned NOT NULL DEFAULT 0,
                member_id int(10) unsigned NOT NULL DEFAULT 0,
                emoji varchar(16) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                UNIQUE KEY comment_member (comment_id, member_id),
                KEY comment_id (comment_id),
                KEY member_id (member_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL);

        return $this->createResult(true, 'Created tl_psa_meetup_comment_reaction table.');
    }
}
