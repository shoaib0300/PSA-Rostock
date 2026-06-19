<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Doctrine\DBAL\Connection;

final class PsaMeetup
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPublishedMeetups(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.*, mem.nickname, mem.firstname, mem.lastname, mem.username
             FROM tl_psa_meetup m
             INNER JOIN tl_member mem ON mem.id = m.member_id
             WHERE m.published = ?
             ORDER BY m.tstamp DESC',
            ['1'],
        );

        $meetups = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $meetups[] = $this->enrichMeetupRow($row, $id);
        }

        return $meetups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getMeetupsByMember(int $memberId): array
    {
        if ($memberId <= 0) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.*, mem.nickname, mem.firstname, mem.lastname, mem.username
             FROM tl_psa_meetup m
             INNER JOIN tl_member mem ON mem.id = m.member_id
             WHERE m.member_id = ?
             ORDER BY m.tstamp DESC',
            [$memberId],
        );

        $meetups = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $meetups[] = $this->enrichMeetupRow($row, $id);
        }

        return $meetups;
    }

    public function getPublishedMeetup(int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT m.*, mem.nickname, mem.firstname, mem.lastname, mem.username
             FROM tl_psa_meetup m
             INNER JOIN tl_member mem ON mem.id = m.member_id
             WHERE m.id = ? AND m.published = ?',
            [$id, '1'],
        );

        if ($row === false) {
            return null;
        }

        return $this->enrichMeetupRow($row, $id);
    }

    /**
     * @param list<string> $pollOptions
     */
    public function createMeetup(
        int $memberId,
        string $title,
        string $description,
        int $meetupDate,
        string $location,
        string $postType = 'meetup',
        string $pollQuestion = '',
        array $pollOptions = [],
    ): int {
        $title = trim($title);

        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }

        if (!\in_array($postType, ['meetup', 'post'], true)) {
            throw new \InvalidArgumentException('Invalid post type.');
        }

        $pollQuestion = trim($pollQuestion);
        $pollOptions = $this->normalizePollOptions($pollOptions);

        if ($pollQuestion !== '' && \count($pollOptions) < 2) {
            throw new \InvalidArgumentException('Poll needs at least two options.');
        }

        if ($pollQuestion === '') {
            $pollOptions = [];
        }

        $time = time();

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_meetup (tstamp, member_id, title, description, meetupDate, location, postType, pollQuestion, published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $time,
                $memberId,
                $title,
                trim($description),
                $postType === 'meetup' ? $meetupDate : 0,
                $postType === 'meetup' ? trim($location) : '',
                $postType,
                $pollQuestion,
                '1',
            ],
        );

        $meetupId = (int) $this->connection->lastInsertId();

        if ($pollQuestion !== '') {
            $this->savePollOptions($meetupId, $pollOptions);
        }

        return $meetupId;
    }

    public function votePoll(int $meetupId, int $optionId, int $memberId): void
    {
        $meetup = $this->getPublishedMeetup($meetupId);

        if ($meetup === null || ($meetup['pollQuestion'] ?? '') === '') {
            throw new \InvalidArgumentException('Invalid poll.');
        }

        $validOption = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup_poll_option WHERE id = ? AND pid = ?',
            [$optionId, $meetupId],
        );

        if (!\is_string($validOption) && !is_int($validOption)) {
            throw new \InvalidArgumentException('Invalid poll option.');
        }

        $time = time();
        $existingId = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup_poll_vote WHERE meetup_id = ? AND member_id = ?',
            [$meetupId, $memberId],
        );

        if (\is_string($existingId) || is_int($existingId)) {
            $this->connection->executeStatement(
                'UPDATE tl_psa_meetup_poll_vote SET option_id = ?, tstamp = ? WHERE id = ?',
                [$optionId, $time, (int) $existingId],
            );

            return;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_meetup_poll_vote (tstamp, meetup_id, option_id, member_id) VALUES (?, ?, ?, ?)',
            [$time, $meetupId, $optionId, $memberId],
        );
    }

    public function getMemberPollVote(int $meetupId, int $memberId): ?int
    {
        if ($memberId <= 0) {
            return null;
        }

        $optionId = $this->connection->fetchOne(
            'SELECT option_id FROM tl_psa_meetup_poll_vote WHERE meetup_id = ? AND member_id = ?',
            [$meetupId, $memberId],
        );

        if (!\is_string($optionId) && !is_int($optionId)) {
            return null;
        }

        return (int) $optionId;
    }

    public function toggleJoin(int $meetupId, int $memberId): bool
    {
        $existingId = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup_join WHERE pid = ? AND member_id = ?',
            [$meetupId, $memberId],
        );

        if (\is_string($existingId) || is_int($existingId)) {
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup_join WHERE id = ?',
                [(int) $existingId],
            );

            return false;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_meetup_join (tstamp, pid, member_id) VALUES (?, ?, ?)',
            [time(), $meetupId, $memberId],
        );

        return true;
    }

    public function isJoined(int $meetupId, int $memberId): bool
    {
        if ($memberId <= 0) {
            return false;
        }

        $id = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_meetup_join WHERE pid = ? AND member_id = ?',
            [$meetupId, $memberId],
        );

        return \is_string($id) || is_int($id);
    }

    /**
     * @return list<string>
     */
    public function getJoinerNames(int $meetupId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.nickname, m.firstname, m.lastname, m.username
             FROM tl_psa_meetup_join j
             INNER JOIN tl_member m ON m.id = j.member_id
             WHERE j.pid = ?
             ORDER BY j.tstamp ASC',
            [$meetupId],
        );

        return array_map(fn (array $row): string => $this->formatMemberName($row), $rows);
    }

    public function getJoinCount(int $meetupId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_psa_meetup_join WHERE pid = ?',
            [$meetupId],
        );
    }

    public function addComment(int $meetupId, int $memberId, string $comment): int
    {
        $comment = trim($comment);

        if ($comment === '') {
            throw new \InvalidArgumentException('Comment is required.');
        }

        $time = time();

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_meetup_comment (tstamp, pid, member_id, comment, published)
             VALUES (?, ?, ?, ?, ?)',
            [$time, $meetupId, $memberId, $comment, '1'],
        );

        return (int) $this->connection->lastInsertId();
    }

    public function deleteComment(int $commentId, int $memberId): bool
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, member_id FROM tl_psa_meetup_comment WHERE id = ?',
            [$commentId],
        );

        if ($row === false || (int) $row['member_id'] !== $memberId) {
            return false;
        }

        $this->connection->executeStatement(
            'DELETE FROM tl_psa_meetup_comment WHERE id = ?',
            [$commentId],
        );

        return true;
    }

    public function deleteMeetup(int $meetupId, int $memberId): bool
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, member_id FROM tl_psa_meetup WHERE id = ?',
            [$meetupId],
        );

        if ($row === false || (int) $row['member_id'] !== $memberId) {
            return false;
        }

        $this->connection->transactional(function () use ($meetupId): void {
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup_poll_vote WHERE meetup_id = ?',
                [$meetupId],
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup_poll_option WHERE pid = ?',
                [$meetupId],
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup_comment WHERE pid = ?',
                [$meetupId],
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup_join WHERE pid = ?',
                [$meetupId],
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_psa_meetup WHERE id = ?',
                [$meetupId],
            );
        });

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getComments(int $meetupId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT c.*, m.nickname, m.firstname, m.lastname, m.username
             FROM tl_psa_meetup_comment c
             INNER JOIN tl_member m ON m.id = c.member_id
             WHERE c.pid = ? AND c.published = ?
             ORDER BY c.tstamp ASC',
            [$meetupId, '1'],
        );

        $comments = [];

        foreach ($rows as $row) {
            $comments[] = [
                'id' => (int) $row['id'],
                'member_id' => (int) $row['member_id'],
                'author' => $this->formatMemberName($row),
                'comment' => (string) ($row['comment'] ?? ''),
                'tstamp' => (int) $row['tstamp'],
                'datim' => date('d.m.Y H:i', (int) $row['tstamp']),
            ];
        }

        return $comments;
    }

    public function getCommentCount(int $meetupId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_psa_meetup_comment WHERE pid = ? AND published = ?',
            [$meetupId, '1'],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getPollData(int $meetupId, string $pollQuestion): array
    {
        if ($pollQuestion === '') {
            return [
                'question' => '',
                'options' => [],
                'totalVotes' => 0,
            ];
        }

        $options = $this->connection->fetchAllAssociative(
            'SELECT o.id, o.label, COUNT(v.id) AS votes
             FROM tl_psa_meetup_poll_option o
             LEFT JOIN tl_psa_meetup_poll_vote v ON v.option_id = o.id
             WHERE o.pid = ?
             GROUP BY o.id, o.label, o.sorting
             ORDER BY o.sorting ASC, o.id ASC',
            [$meetupId],
        );

        $totalVotes = 0;
        $normalized = [];

        foreach ($options as $option) {
            $votes = (int) ($option['votes'] ?? 0);
            $totalVotes += $votes;
            $normalized[] = [
                'id' => (int) $option['id'],
                'label' => (string) ($option['label'] ?? ''),
                'votes' => $votes,
            ];
        }

        foreach ($normalized as &$option) {
            $option['percent'] = $totalVotes > 0 ? (int) round(($option['votes'] / $totalVotes) * 100) : 0;
        }
        unset($option);

        return [
            'question' => $pollQuestion,
            'options' => $normalized,
            'totalVotes' => $totalVotes,
        ];
    }

    /**
     * @param list<string> $options
     */
    private function savePollOptions(int $meetupId, array $options): void
    {
        $sorting = 0;

        foreach ($options as $label) {
            $this->connection->executeStatement(
                'INSERT INTO tl_psa_meetup_poll_option (pid, label, sorting) VALUES (?, ?, ?)',
                [$meetupId, $label, $sorting],
            );
            $sorting += 128;
        }
    }

    /**
     * @param list<string> $options
     *
     * @return list<string>
     */
    private function normalizePollOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $option) {
            $label = trim((string) $option);

            if ($label === '') {
                continue;
            }

            $normalized[] = mb_substr($label, 0, 255);
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function enrichMeetupRow(array $row, int $id): array
    {
        $meetupDate = (int) ($row['meetupDate'] ?? 0);
        $postType = (string) ($row['postType'] ?? 'meetup');
        $pollQuestion = trim((string) ($row['pollQuestion'] ?? ''));

        if (!\in_array($postType, ['meetup', 'post'], true)) {
            $postType = 'meetup';
        }

        return [
            'id' => $id,
            'member_id' => (int) $row['member_id'],
            'author' => $this->formatMemberName($row),
            'title' => (string) ($row['title'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'postType' => $postType,
            'isMeetup' => $postType === 'meetup',
            'isPost' => $postType === 'post',
            'meetupDate' => $meetupDate,
            'meetupDateFormatted' => $meetupDate > 0 ? date('d.m.Y H:i', $meetupDate) : '',
            'location' => (string) ($row['location'] ?? ''),
            'tstamp' => (int) $row['tstamp'],
            'postedAt' => date('d.m.Y H:i', (int) $row['tstamp']),
            'isPublished' => (string) ($row['published'] ?? '') === '1',
            'joinCount' => $postType === 'meetup' ? $this->getJoinCount($id) : 0,
            'joiners' => $postType === 'meetup' ? $this->getJoinerNames($id) : [],
            'comments' => $this->getComments($id),
            'commentCount' => $this->getCommentCount($id),
            'poll' => $this->getPollData($id, $pollQuestion),
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function formatMemberName(array $row): string
    {
        $nickname = trim((string) ($row['nickname'] ?? ''));

        if ($nickname !== '') {
            return $nickname;
        }

        $fullName = trim((string) ($row['firstname'] ?? '').' '.(string) ($row['lastname'] ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        return (string) ($row['username'] ?? 'Member');
    }
}
