<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:seed-vote',
    description: 'Creates sample vote positions, campaigns, candidates, and example ballots.',
)]
class SeedVoteCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Delete existing vote demo data before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_campaign'])) {
            $io->error('Vote tables missing. Run `php vendor/bin/contao-console contao:migrate` first.');

            return Command::FAILURE;
        }

        if ($input->getOption('reset')) {
            $this->resetDemoData($io);
        }

        $now = time();
        $today = strtotime('today', $now);

        $presidentId = $this->ensureReason($io, 'President', 'Leads the PSA board and represents members.', 128);
        $treasurerId = $this->ensureReason($io, 'Treasurer', 'Manages finances and membership fees.', 256);
        $eventsId = $this->ensureReason($io, 'Events Lead', 'Organises meetups and community events.', 384);

        $activeCampaignId = $this->ensureCampaign(
            $io,
            'Board Election 2026',
            '<p>Vote for the next PSA board. You can pick one candidate per position.</p>',
            $today,
            strtotime('+14 days 23:59:59', $today),
            'after_vote',
            true,
        );

        $endedCampaignId = $this->ensureCampaign(
            $io,
            'Summer Event Lead 2025',
            '<p>This vote is closed. Results are shown for reference.</p>',
            strtotime('-30 days', $today),
            strtotime('-7 days 23:59:59', $today),
            'always',
            true,
        );

        $this->purgeCandidates($activeCampaignId);
        $this->purgeCandidates($endedCampaignId);
        $this->connection->executeStatement('DELETE FROM tl_psa_vote_ballot WHERE campaign_id IN (?, ?)', [$activeCampaignId, $endedCampaignId]);

        $activeCandidates = [
            $this->insertCandidate($activeCampaignId, $presidentId, 'Anna Becker', 'Experienced member since 2022.', 128),
            $this->insertCandidate($activeCampaignId, $presidentId, 'Jonas Klein', 'Focused on transparency and outreach.', 256),
            $this->insertCandidate($activeCampaignId, $treasurerId, 'Maria Schulz', 'Background in finance and budgeting.', 384),
            $this->insertCandidate($activeCampaignId, $treasurerId, 'Tim Wagner', 'Keeps our costs lean and fair.', 512),
            $this->insertCandidate($activeCampaignId, $eventsId, 'Lena Hoffmann', 'Organised three successful meetups.', 640),
            $this->insertCandidate($activeCampaignId, $eventsId, 'Paul Richter', 'Brings fresh event ideas.', 768),
        ];

        $endedCandidates = [
            $this->insertCandidate($endedCampaignId, $eventsId, 'Sofia Meyer', 'Led the harbour walk series.', 128),
            $this->insertCandidate($endedCampaignId, $eventsId, 'Felix Braun', 'Strong network in local clubs.', 256),
        ];

        $members = $this->connection->fetchFirstColumn(
            'SELECT id FROM tl_member WHERE login = ? AND disable != ? ORDER BY id ASC LIMIT 8',
            ['1', '1'],
        );

        if ($members === []) {
            $io->warning('No active members found — campaigns created without sample ballots.');
        } else {
            $this->seedBallots($activeCampaignId, $members, [
                $presidentId => $activeCandidates[0],
                $treasurerId => $activeCandidates[2],
                $eventsId => $activeCandidates[5],
            ], $now - 3600);

            $this->seedBallots($activeCampaignId, \array_slice($members, 1, 4), [
                $presidentId => $activeCandidates[1],
                $treasurerId => $activeCandidates[3],
                $eventsId => $activeCandidates[4],
            ], $now - 1800);

            $this->seedBallots($endedCampaignId, \array_slice($members, 0, 5), [
                $eventsId => $endedCandidates[0],
            ], strtotime('-10 days', $now));

            $this->seedBallots($endedCampaignId, \array_slice($members, 2, 3), [
                $eventsId => $endedCandidates[1],
            ], strtotime('-9 days', $now));
        }

        $io->success(sprintf(
            'Vote demo ready. Active campaign id %d, ended campaign id %d. Open /vote on the site.',
            $activeCampaignId,
            $endedCampaignId,
        ));

        return Command::SUCCESS;
    }

    private function resetDemoData(SymfonyStyle $io): void
    {
        $this->connection->executeStatement('DELETE FROM tl_psa_vote_ballot');
        $this->connection->executeStatement('DELETE FROM tl_psa_vote_candidate');
        $this->connection->executeStatement('DELETE FROM tl_psa_vote_campaign');
        $this->connection->executeStatement("DELETE FROM tl_psa_vote_reason WHERE title IN ('President', 'Treasurer', 'Events Lead')");
        $io->writeln('Cleared existing vote demo data.');
    }

    private function ensureReason(SymfonyStyle $io, string $title, string $description, int $sorting): int
    {
        $id = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_vote_reason WHERE title = ? ORDER BY id ASC LIMIT 1',
            [$title],
        );

        if (is_numeric($id)) {
            $this->connection->executeStatement(
                'UPDATE tl_psa_vote_reason SET tstamp = ?, sorting = ?, description = ?, published = ? WHERE id = ?',
                [time(), $sorting, $description, '1', (int) $id],
            );
            $io->writeln('Updated position "'.$title.'" (id '.(int) $id.').');

            return (int) $id;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_vote_reason (tstamp, sorting, title, description, published) VALUES (?, ?, ?, ?, ?)',
            [time(), $sorting, $title, $description, '1'],
        );

        $id = (int) $this->connection->lastInsertId();
        $io->writeln('Created position "'.$title.'" (id '.$id.').');

        return $id;
    }

    private function ensureCampaign(
        SymfonyStyle $io,
        string $title,
        string $description,
        int $startDate,
        int $endDate,
        string $showResults,
        bool $published,
    ): int {
        $id = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_vote_campaign WHERE title = ? ORDER BY id ASC LIMIT 1',
            [$title],
        );

        if (is_numeric($id)) {
            $this->connection->executeStatement(
                'UPDATE tl_psa_vote_campaign SET tstamp = ?, description = ?, startDate = ?, endDate = ?, showResults = ?, published = ? WHERE id = ?',
                [time(), $description, $startDate, $endDate, $showResults, $published ? '1' : '0', (int) $id],
            );
            $io->writeln('Updated campaign "'.$title.'" (id '.(int) $id.').');

            return (int) $id;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_vote_campaign (tstamp, title, description, startDate, endDate, showResults, published) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [time(), $title, $description, $startDate, $endDate, $showResults, $published ? '1' : '0'],
        );

        $id = (int) $this->connection->lastInsertId();
        $io->writeln('Created campaign "'.$title.'" (id '.$id.').');

        return $id;
    }

    private function purgeCandidates(int $campaignId): void
    {
        $this->connection->executeStatement('DELETE FROM tl_psa_vote_candidate WHERE pid = ?', [$campaignId]);
    }

    private function insertCandidate(
        int $campaignId,
        int $reasonId,
        string $name,
        string $description,
        int $sorting,
    ): int {
        $this->connection->executeStatement(
            'INSERT INTO tl_psa_vote_candidate (pid, tstamp, sorting, reason_id, name, position, description, published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$campaignId, time(), $sorting, $reasonId, $name, '', $description, '1'],
        );

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param list<int|string>          $memberIds
     * @param array<int, int>           $votes reasonId => candidateId
     */
    private function seedBallots(int $campaignId, array $memberIds, array $votes, int $tstamp): void
    {
        foreach ($memberIds as $memberId) {
            $memberId = (int) $memberId;

            if ($memberId <= 0) {
                continue;
            }

            foreach ($votes as $reasonId => $candidateId) {
                $this->connection->executeStatement(
                    'INSERT INTO tl_psa_vote_ballot (tstamp, campaign_id, reason_id, candidate_id, member_id)
                     VALUES (?, ?, ?, ?, ?)',
                    [$tstamp, $campaignId, (int) $reasonId, (int) $candidateId, $memberId],
                );
            }
        }
    }
}
