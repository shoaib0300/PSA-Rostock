<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaMeetupCommentReactionOnePerMemberMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup_comment_reaction'])) {
            return false;
        }

        $indexes = $this->connection->createSchemaManager()->listTableIndexes('tl_psa_meetup_comment_reaction');

        return isset($indexes['comment_member_emoji']) && !isset($indexes['comment_member']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(<<<'SQL'
            DELETE r1 FROM tl_psa_meetup_comment_reaction r1
            INNER JOIN tl_psa_meetup_comment_reaction r2
                ON r1.comment_id = r2.comment_id
                AND r1.member_id = r2.member_id
                AND r1.id < r2.id
            SQL);

        $this->connection->executeStatement(
            'ALTER TABLE tl_psa_meetup_comment_reaction DROP INDEX comment_member_emoji'
        );
        $this->connection->executeStatement(
            'ALTER TABLE tl_psa_meetup_comment_reaction ADD UNIQUE KEY comment_member (comment_id, member_id)'
        );

        return $this->createResult(true, 'Limited comment reactions to one emoji per member.');
    }
}
