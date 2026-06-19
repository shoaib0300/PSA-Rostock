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
    /** @var list<string> */
    private const DEMO_CAMPAIGN_TITLES = [
        'Board Election 2026',
        'Community Survey Vote Q2 2026',
        'Venue Preference Poll',
        'Winter Social Planner',
        'AGM Board Nominations 2026',
        'Summer Event Lead 2025',
        'Autumn Treasurer Vote 2024',
        'Spring Communications Vote 2025',
    ];

    /** @var list<string> */
    private const DEMO_REASON_TITLES = [
        'President',
        'Treasurer',
        'Events Lead',
        'Secretary',
        'Communications Lead',
    ];

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
        $secretaryId = $this->ensureReason($io, 'Secretary', 'Keeps minutes and member records up to date.', 512);
        $commsId = $this->ensureReason($io, 'Communications Lead', 'Runs newsletters and social channels.', 640);

        $campaignIds = [];

        $campaignIds['active_board'] = $this->ensureCampaign(
            $io,
            'Board Election 2026',
            '<p>Vote for the next PSA board. You can pick one candidate per position.</p>',
            $today,
            strtotime('+14 days 23:59:59', $today),
            'after_vote',
            true,
        );

        $campaignIds['active_survey'] = $this->ensureCampaign(
            $io,
            'Community Survey Vote Q2 2026',
            '<p>Help us choose the next community workshop theme.</p>',
            $today,
            strtotime('+21 days 23:59:59', $today),
            'after_end',
            true,
        );

        $campaignIds['active_venue'] = $this->ensureCampaign(
            $io,
            'Venue Preference Poll',
            '<p>Pick the location for our summer meetup.</p>',
            $today,
            strtotime('+7 days 23:59:59', $today),
            'always',
            true,
        );

        $campaignIds['upcoming_winter'] = $this->ensureCampaign(
            $io,
            'Winter Social Planner',
            '<p>Upcoming vote for who will organise the winter social.</p>',
            strtotime('+7 days', $today),
            strtotime('+21 days 23:59:59', $today),
            'after_vote',
            true,
        );

        $campaignIds['upcoming_agm'] = $this->ensureCampaign(
            $io,
            'AGM Board Nominations 2026',
            '<p>Nominations for the annual general meeting board slate.</p>',
            strtotime('+30 days', $today),
            strtotime('+45 days 23:59:59', $today),
            'after_end',
            true,
        );

        $campaignIds['ended_summer'] = $this->ensureCampaign(
            $io,
            'Summer Event Lead 2025',
            '<p>This vote is closed. Results are shown for reference.</p>',
            strtotime('-30 days', $today),
            strtotime('-7 days 23:59:59', $today),
            'always',
            true,
        );

        $campaignIds['ended_autumn'] = $this->ensureCampaign(
            $io,
            'Autumn Treasurer Vote 2024',
            '<p>Closed treasurer election from last autumn.</p>',
            strtotime('-120 days', $today),
            strtotime('-90 days 23:59:59', $today),
            'always',
            true,
        );

        $campaignIds['ended_spring'] = $this->ensureCampaign(
            $io,
            'Spring Communications Vote 2025',
            '<p>Closed vote for the communications lead role.</p>',
            strtotime('-75 days', $today),
            strtotime('-60 days 23:59:59', $today),
            'after_vote',
            true,
        );

        foreach ($campaignIds as $campaignId) {
            $this->purgeCandidates($campaignId);
            $this->connection->executeStatement('DELETE FROM tl_psa_vote_ballot WHERE campaign_id = ?', [$campaignId]);
        }

        $activeBoardCandidates = [
            $this->insertCandidate($campaignIds['active_board'], $presidentId, 'Anna Becker', 'Experienced member since 2022.', 128),
            $this->insertCandidate($campaignIds['active_board'], $presidentId, 'Jonas Klein', 'Focused on transparency and outreach.', 256),
            $this->insertCandidate($campaignIds['active_board'], $treasurerId, 'Maria Schulz', 'Background in finance and budgeting.', 384),
            $this->insertCandidate($campaignIds['active_board'], $treasurerId, 'Tim Wagner', 'Keeps our costs lean and fair.', 512),
            $this->insertCandidate($campaignIds['active_board'], $eventsId, 'Lena Hoffmann', 'Organised three successful meetups.', 640),
            $this->insertCandidate($campaignIds['active_board'], $eventsId, 'Paul Richter', 'Brings fresh event ideas.', 768),
        ];

        $activeSurveyCandidates = [
            $this->insertCandidate($campaignIds['active_survey'], $eventsId, 'Photography Walk', 'Street photography around the harbour.', 128),
            $this->insertCandidate($campaignIds['active_survey'], $eventsId, 'Cooking Night', 'Shared kitchen evening with local dishes.', 256),
            $this->insertCandidate($campaignIds['active_survey'], $eventsId, 'Board Games', 'Casual games night for new members.', 384),
        ];

        $activeVenueCandidates = [
            $this->insertCandidate($campaignIds['active_venue'], $eventsId, 'Harbour Terrace', 'Outdoor space with sunset views.', 128),
            $this->insertCandidate($campaignIds['active_venue'], $eventsId, 'Community Hall', 'Central location with kitchen access.', 256),
        ];

        $upcomingWinterCandidates = [
            $this->insertCandidate($campaignIds['upcoming_winter'], $eventsId, 'Nina Krause', 'Planned last year\'s winter dinner.', 128),
            $this->insertCandidate($campaignIds['upcoming_winter'], $eventsId, 'Oliver Stein', 'Strong contacts with local venues.', 256),
        ];

        $upcomingAgmCandidates = [
            $this->insertCandidate($campaignIds['upcoming_agm'], $presidentId, 'Clara Weiss', 'Two terms on the advisory board.', 128),
            $this->insertCandidate($campaignIds['upcoming_agm'], $presidentId, 'David Lorenz', 'Focused on growing membership.', 256),
            $this->insertCandidate($campaignIds['upcoming_agm'], $secretaryId, 'Eva Brandt', 'Detail-oriented and organised.', 384),
            $this->insertCandidate($campaignIds['upcoming_agm'], $secretaryId, 'Markus Fuchs', 'Keeps meetings on track.', 512),
        ];

        $endedSummerCandidates = [
            $this->insertCandidate($campaignIds['ended_summer'], $eventsId, 'Sofia Meyer', 'Led the harbour walk series.', 128),
            $this->insertCandidate($campaignIds['ended_summer'], $eventsId, 'Felix Braun', 'Strong network in local clubs.', 256),
        ];

        $endedAutumnCandidates = [
            $this->insertCandidate($campaignIds['ended_autumn'], $treasurerId, 'Helena Vogt', 'Former club accountant.', 128),
            $this->insertCandidate($campaignIds['ended_autumn'], $treasurerId, 'Jan Berger', 'Transparent budgeting advocate.', 256),
        ];

        $endedSpringCandidates = [
            $this->insertCandidate($campaignIds['ended_spring'], $commsId, 'Laura Peters', 'Grew our newsletter audience.', 128),
            $this->insertCandidate($campaignIds['ended_spring'], $commsId, 'Simon Hart', 'Active on community channels.', 256),
            $this->insertCandidate($campaignIds['ended_spring'], $commsId, 'Yasmin Ali', 'Strong visual storytelling.', 384),
        ];

        $members = $this->connection->fetchFirstColumn(
            'SELECT id FROM tl_member WHERE login = ? AND disable != ? ORDER BY id ASC LIMIT 12',
            ['1', '1'],
        );

        if ($members === []) {
            $io->warning('No active members found — campaigns created without sample ballots.');
        } else {
            $this->seedBallots($campaignIds['active_board'], $members, [
                $presidentId => $activeBoardCandidates[0],
                $treasurerId => $activeBoardCandidates[2],
                $eventsId => $activeBoardCandidates[5],
            ], $now - 3600);

            $this->seedBallots($campaignIds['active_board'], \array_slice($members, 1, 4), [
                $presidentId => $activeBoardCandidates[1],
                $treasurerId => $activeBoardCandidates[3],
                $eventsId => $activeBoardCandidates[4],
            ], $now - 1800);

            $this->seedBallots($campaignIds['active_survey'], \array_slice($members, 0, 6), [
                $eventsId => $activeSurveyCandidates[0],
            ], $now - 2400);

            $this->seedBallots($campaignIds['active_survey'], \array_slice($members, 2, 4), [
                $eventsId => $activeSurveyCandidates[1],
            ], $now - 1200);

            $this->seedBallots($campaignIds['active_venue'], \array_slice($members, 0, 5), [
                $eventsId => $activeVenueCandidates[0],
            ], $now - 900);

            $this->seedBallots($campaignIds['active_venue'], \array_slice($members, 3, 3), [
                $eventsId => $activeVenueCandidates[1],
            ], $now - 600);

            $this->seedBallots($campaignIds['ended_summer'], \array_slice($members, 0, 5), [
                $eventsId => $endedSummerCandidates[0],
            ], strtotime('-10 days', $now));

            $this->seedBallots($campaignIds['ended_summer'], \array_slice($members, 2, 3), [
                $eventsId => $endedSummerCandidates[1],
            ], strtotime('-9 days', $now));

            $this->seedBallots($campaignIds['ended_autumn'], \array_slice($members, 0, 4), [
                $treasurerId => $endedAutumnCandidates[0],
            ], strtotime('-95 days', $now));

            $this->seedBallots($campaignIds['ended_autumn'], \array_slice($members, 1, 3), [
                $treasurerId => $endedAutumnCandidates[1],
            ], strtotime('-92 days', $now));

            $this->seedBallots($campaignIds['ended_spring'], \array_slice($members, 0, 7), [
                $commsId => $endedSpringCandidates[0],
            ], strtotime('-65 days', $now));

            $this->seedBallots($campaignIds['ended_spring'], \array_slice($members, 2, 4), [
                $commsId => $endedSpringCandidates[2],
            ], strtotime('-63 days', $now));
        }

        $io->success(sprintf(
            'Vote demo ready: %d active, %d upcoming, %d ended campaigns. Open /vote on the site.',
            3,
            2,
            3,
        ));

        $io->listing([
            'Active: Board Election 2026 (#'.$campaignIds['active_board'].')',
            'Active: Community Survey Vote Q2 2026 (#'.$campaignIds['active_survey'].')',
            'Active: Venue Preference Poll (#'.$campaignIds['active_venue'].')',
            'Upcoming: Winter Social Planner (#'.$campaignIds['upcoming_winter'].')',
            'Upcoming: AGM Board Nominations 2026 (#'.$campaignIds['upcoming_agm'].')',
            'Ended: Summer Event Lead 2025 (#'.$campaignIds['ended_summer'].')',
            'Ended: Autumn Treasurer Vote 2024 (#'.$campaignIds['ended_autumn'].')',
            'Ended: Spring Communications Vote 2025 (#'.$campaignIds['ended_spring'].')',
        ]);

        return Command::SUCCESS;
    }

    private function resetDemoData(SymfonyStyle $io): void
    {
        $campaignIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM tl_psa_vote_campaign WHERE title IN ('.implode(',', array_fill(0, \count(self::DEMO_CAMPAIGN_TITLES), '?')).')',
            self::DEMO_CAMPAIGN_TITLES,
        );

        if ($campaignIds !== []) {
            $placeholders = implode(',', array_fill(0, \count($campaignIds), '?'));
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_vote_ballot WHERE campaign_id IN ('.$placeholders.')',
                $campaignIds,
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_vote_candidate WHERE pid IN ('.$placeholders.')',
                $campaignIds,
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_vote_campaign WHERE id IN ('.$placeholders.')',
                $campaignIds,
            );
        }

        $this->connection->executeStatement(
            'DELETE FROM tl_psa_vote_reason WHERE title IN ('.implode(',', array_fill(0, \count(self::DEMO_REASON_TITLES), '?')).')',
            self::DEMO_REASON_TITLES,
        );

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
     * @param list<int|string> $memberIds
     * @param array<int, int>  $votes reasonId => candidateId
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
