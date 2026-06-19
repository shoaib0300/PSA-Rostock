<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PsaVoteCampaignDateFixMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_campaign'])) {
            return false;
        }

        $row = $this->connection->fetchAssociative(
            'SELECT id FROM tl_psa_vote_campaign WHERE (startDate BETWEEN 20000101 AND 29991231) OR (endDate BETWEEN 20000101 AND 29991231) LIMIT 1',
        );

        return $row !== false;
    }

    public function run(): MigrationResult
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, startDate, endDate FROM tl_psa_vote_campaign
             WHERE (startDate BETWEEN 20000101 AND 29991231) OR (endDate BETWEEN 20000101 AND 29991231)',
        );

        foreach ($rows as $row) {
            $start = $this->fixDate((int) $row['startDate']);
            $end = $this->fixDate((int) $row['endDate'], true);

            $this->connection->executeStatement(
                'UPDATE tl_psa_vote_campaign SET startDate = ?, endDate = ? WHERE id = ?',
                [$start, $end, (int) $row['id']],
            );
        }

        return $this->createResult(true, 'Fixed PSA vote campaign date timestamps.');
    }

    private function fixDate(int $value, bool $endOfDay = false): int
    {
        if ($value < 20000101 || $value > 29991231) {
            return $value;
        }

        $timestamp = strtotime(sprintf(
            '%04d-%02d-%02d',
            intdiv($value, 10000),
            intdiv($value % 10000, 100),
            $value % 100,
        ).($endOfDay ? ' 23:59:59' : ' 00:00:00'));

        return $timestamp > 0 ? $timestamp : 0;
    }
}
