<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Doctrine\DBAL\Connection;
use Rostock\CustomElementsBundle\Classes\PsaMeetup;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:seed-meetups',
    description: 'Creates sample meetup outings and community help posts with comments and RSVPs.',
)]
class SeedMeetupsCommand extends Command
{
    /** @var list<string> */
    private const DEMO_TITLES = [
        'Harbor sunset walk',
        'Board games café evening',
        'Summer potluck in the park',
        'German–English language swap',
        'Ride share to Hamburg?',
        'Tips for city registration',
        'Free bookshelf in Lütten Klein',
    ];

    /** @var list<array{username: string, firstname: string, lastname: string, nickname: string, email: string}> */
    private const DEMO_MEMBERS = [
        ['username' => 'psa_demo_anna', 'firstname' => 'Anna', 'lastname' => 'Becker', 'nickname' => 'Anna B.', 'email' => 'anna.demo@psa-rostock.example'],
        ['username' => 'psa_demo_jonas', 'firstname' => 'Jonas', 'lastname' => 'Klein', 'nickname' => 'Jonas', 'email' => 'jonas.demo@psa-rostock.example'],
        ['username' => 'psa_demo_lena', 'firstname' => 'Lena', 'lastname' => 'Hoffmann', 'nickname' => 'Lena H.', 'email' => 'lena.demo@psa-rostock.example'],
        ['username' => 'psa_demo_paul', 'firstname' => 'Paul', 'lastname' => 'Richter', 'nickname' => 'Paul R.', 'email' => 'paul.demo@psa-rostock.example'],
        ['username' => 'psa_demo_maria', 'firstname' => 'Maria', 'lastname' => 'Schulz', 'nickname' => 'Maria', 'email' => 'maria.demo@psa-rostock.example'],
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly PsaMeetup $meetup,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Delete existing meetup demo posts before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_meetup'])) {
            $io->error('Meetup tables missing. Run `php vendor/bin/contao-console contao:migrate` first.');

            return Command::FAILURE;
        }

        if ($input->getOption('reset')) {
            $this->resetDemoData($io);
        }

        $authors = $this->ensureDemoMembers($io);
        $now = time();

        $harborId = $this->createDemoMeetup(
            $authors[0],
            'Harbor sunset walk',
            "Let's meet at Kröpeliner Tor and stroll down to the harbor for sunset photos.\nBring a light jacket — it can get breezy by the water.",
            strtotime('+3 days 18:30', $now),
            'Kröpeliner Tor, Rostock',
            $now - 86400 * 2,
        );
        $this->seedJoins($harborId, [$authors[1], $authors[2], $authors[3]]);
        $this->seedComments($harborId, [
            [$authors[1], 'Sounds lovely — count me in!'],
            [$authors[2], 'Can we grab ice cream on the way back?'],
        ]);

        $boardGamesId = $this->createDemoMeetup(
            $authors[1],
            'Board games café evening',
            "Casual board games night at Café Central.\nBeginners welcome — we'll explain the rules.",
            strtotime('+6 days 19:00', $now),
            'Café Central, Rostock',
            $now - 86400,
            'Which game should we play?',
            ['Catan', 'Codenames', 'Ticket to Ride', 'Uno'],
        );
        $this->seedPollVotes($boardGamesId, [
            [$authors[0], 0],
            [$authors[2], 1],
            [$authors[3], 1],
            [$authors[4], 2],
        ]);
        $this->seedJoins($boardGamesId, [$authors[0], $authors[1], $authors[4]]);
        $this->seedComments($boardGamesId, [
            [$authors[4], 'Codenames is always a hit with new people.'],
        ]);

        $potluckId = $this->createDemoMeetup(
            $authors[2],
            'Summer potluck in the park',
            "Bring one dish to share — homemade or shop-bought, anything goes.\nWe'll meet near the fountain and find a spot on the grass.",
            strtotime('+10 days 17:00', $now),
            'Stadtpark Rostock, near the fountain',
            $now - 3600 * 5,
        );
        $this->seedJoins($potluckId, [$authors[0], $authors[1], $authors[3], $authors[4]]);

        $this->createDemoPost(
            $authors[3],
            'German–English language swap',
            "Hi everyone — I'm looking for someone to practise German with once a week.\nHappy to help with English in return. Weekday evenings work best for me.",
            $now - 86400 * 4,
        );
        $this->createDemoPost(
            $authors[4],
            'Ride share to Hamburg?',
            "Is anyone driving to Hamburg next Saturday morning?\nHappy to chip in for fuel and snacks.",
            $now - 86400 * 3,
        );
        $this->createDemoPost(
            $authors[0],
            'Tips for city registration',
            "I just moved to Rostock and need to register at the Bürgeramt.\nAny tips on documents, appointments, or which office to use?",
            $now - 86400 * 2,
        );
        $helpPostId = $this->createDemoPost(
            $authors[1],
            'Free bookshelf in Lütten Klein',
            "We're giving away a small IKEA bookshelf (Billy, white).\nPick-up only in Lütten Klein — first reply gets it.",
            $now - 86400,
        );
        $this->seedComments($helpPostId, [
            [$authors[2], 'Still available? I can pick it up tomorrow evening.'],
            [$authors[1], 'Yes — I will message you the address.'],
        ]);

        $io->success(sprintf(
            'Meetup demo ready: 3 meetups and 4 help posts. Open /meetups on the site.',
        ));

        $io->listing([
            'Meetup: Harbor sunset walk',
            'Meetup: Board games café evening (with poll)',
            'Meetup: Summer potluck in the park',
            'Post: German–English language swap',
            'Post: Ride share to Hamburg?',
            'Post: Tips for city registration',
            'Post: Free bookshelf in Lütten Klein',
        ]);

        return Command::SUCCESS;
    }

    private function resetDemoData(SymfonyStyle $io): void
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT id FROM tl_psa_meetup WHERE title IN ('.implode(',', array_fill(0, \count(self::DEMO_TITLES), '?')).')',
            self::DEMO_TITLES,
        );

        foreach ($ids as $id) {
            $meetupId = (int) $id;
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup_poll_vote WHERE meetup_id = ?', [$meetupId]);
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup_poll_option WHERE pid = ?', [$meetupId]);
            $this->connection->executeStatement(
                'DELETE r FROM tl_psa_meetup_comment_reaction r
                 INNER JOIN tl_psa_meetup_comment c ON c.id = r.comment_id
                 WHERE c.pid = ?',
                [$meetupId],
            );
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup_comment WHERE pid = ?', [$meetupId]);
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup_join WHERE pid = ?', [$meetupId]);
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup WHERE id = ?', [$meetupId]);
        }

        $io->writeln('Cleared '.(\count($ids)).' demo meetup/post(s).');
    }

    /**
     * @return list<int>
     */
    private function ensureDemoMembers(SymfonyStyle $io): array
    {
        $ids = [];

        foreach (self::DEMO_MEMBERS as $member) {
            $existingId = $this->connection->fetchOne(
                'SELECT id FROM tl_member WHERE username = ? LIMIT 1',
                [$member['username']],
            );

            if (is_numeric($existingId)) {
                $this->connection->executeStatement(
                    'UPDATE tl_member SET tstamp = ?, firstname = ?, lastname = ?, nickname = ?, email = ? WHERE id = ?',
                    [time(), $member['firstname'], $member['lastname'], $member['nickname'], $member['email'], (int) $existingId],
                );
                $ids[] = (int) $existingId;

                continue;
            }

            $this->connection->executeStatement(
                'INSERT INTO tl_member (tstamp, firstname, lastname, nickname, username, email, login, disable, dateAdded)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    time(),
                    $member['firstname'],
                    $member['lastname'],
                    $member['nickname'],
                    $member['username'],
                    $member['email'],
                    0,
                    1,
                    time(),
                ],
            );

            $ids[] = (int) $this->connection->lastInsertId();
        }

        $io->writeln('Using '.(\count($ids)).' demo author profile(s).');

        return $ids;
    }

    /**
     * @param list<string> $pollOptions
     */
    private function createDemoMeetup(
        int $authorId,
        string $title,
        string $description,
        int $meetupDate,
        string $location,
        int $postedAt,
        string $pollQuestion = '',
        array $pollOptions = [],
    ): int {
        $existingId = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup WHERE title = ? LIMIT 1',
            [$title],
        );

        if (is_numeric($existingId)) {
            $this->purgeMeetupChildren((int) $existingId);
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup WHERE id = ?', [(int) $existingId]);
        }

        $meetupId = $this->meetup->createMeetup(
            $authorId,
            $title,
            $description,
            $meetupDate,
            $location,
            'meetup',
            $pollQuestion,
            $pollOptions,
        );

        $this->connection->executeStatement(
            'UPDATE tl_psa_meetup SET tstamp = ? WHERE id = ?',
            [$postedAt, $meetupId],
        );

        return $meetupId;
    }

    private function createDemoPost(int $authorId, string $title, string $description, int $postedAt): int
    {
        $existingId = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup WHERE title = ? LIMIT 1',
            [$title],
        );

        if (is_numeric($existingId)) {
            $this->purgeMeetupChildren((int) $existingId);
            $this->connection->executeStatement('DELETE FROM tl_psa_meetup WHERE id = ?', [(int) $existingId]);
        }

        $postId = $this->meetup->createMeetup(
            $authorId,
            $title,
            $description,
            0,
            '',
            'post',
        );

        $this->connection->executeStatement(
            'UPDATE tl_psa_meetup SET tstamp = ? WHERE id = ?',
            [$postedAt, $postId],
        );

        return $postId;
    }

    private function purgeMeetupChildren(int $meetupId): void
    {
        $this->connection->executeStatement('DELETE FROM tl_psa_meetup_poll_vote WHERE meetup_id = ?', [$meetupId]);
        $this->connection->executeStatement('DELETE FROM tl_psa_meetup_poll_option WHERE pid = ?', [$meetupId]);
        $this->connection->executeStatement(
            'DELETE r FROM tl_psa_meetup_comment_reaction r
             INNER JOIN tl_psa_meetup_comment c ON c.id = r.comment_id
             WHERE c.pid = ?',
            [$meetupId],
        );
        $this->connection->executeStatement('DELETE FROM tl_psa_meetup_comment WHERE pid = ?', [$meetupId]);
        $this->connection->executeStatement('DELETE FROM tl_psa_meetup_join WHERE pid = ?', [$meetupId]);
    }

    /**
     * @param list<int> $memberIds
     */
    private function seedJoins(int $meetupId, array $memberIds): void
    {
        foreach ($memberIds as $memberId) {
            $this->meetup->setJoinStatus($meetupId, (int) $memberId, 'join');
        }
    }

    /**
     * @param list<array{0: int, 1: string}> $comments
     */
    private function seedComments(int $meetupId, array $comments): void
    {
        foreach ($comments as [$memberId, $text]) {
            $this->meetup->addComment($meetupId, (int) $memberId, $text);
        }
    }

    /**
     * @param list<array{0: int, 1: int}> $votes memberId => option index
     */
    private function seedPollVotes(int $meetupId, array $votes): void
    {
        $optionIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM tl_psa_meetup_poll_option WHERE pid = ? ORDER BY sorting ASC, id ASC',
            [$meetupId],
        );

        foreach ($votes as [$memberId, $optionIndex]) {
            $optionId = (int) ($optionIds[$optionIndex] ?? 0);

            if ($optionId <= 0) {
                continue;
            }

            $this->meetup->votePoll($meetupId, $optionId, (int) $memberId);
        }
    }
}
