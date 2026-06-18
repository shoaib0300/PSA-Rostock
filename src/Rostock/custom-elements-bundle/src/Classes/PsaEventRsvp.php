<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Doctrine\DBAL\Connection;

final class PsaEventRsvp
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array{yes: int, no: int}
     */
    public function getCounts(int $eventId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT status, COUNT(*) AS total FROM tl_psa_event_rsvp WHERE event_id = ? GROUP BY status',
            [$eventId],
        );

        $counts = ['yes' => 0, 'no' => 0];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');

            if (isset($counts[$status])) {
                $counts[$status] = (int) $row['total'];
            }
        }

        return $counts;
    }

    /**
     * @return array{yes: list<string>, no: list<string>}
     */
    public function getVoterLists(int $eventId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT r.status, m.nickname, m.firstname, m.lastname, m.username
             FROM tl_psa_event_rsvp r
             INNER JOIN tl_member m ON m.id = r.member_id
             WHERE r.event_id = ?
             ORDER BY r.tstamp ASC',
            [$eventId],
        );

        $lists = ['yes' => [], 'no' => []];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');

            if (!isset($lists[$status])) {
                continue;
            }

            $lists[$status][] = $this->formatMemberName($row);
        }

        return $lists;
    }

    public function getVote(int $eventId, int $memberId): ?string
    {
        $status = $this->connection->fetchOne(
            'SELECT status FROM tl_psa_event_rsvp WHERE event_id = ? AND member_id = ?',
            [$eventId, $memberId],
        );

        if (!\is_string($status) || !\in_array($status, ['yes', 'no'], true)) {
            return null;
        }

        return $status;
    }

    public function vote(int $eventId, int $memberId, string $status): void
    {
        if (!\in_array($status, ['yes', 'no'], true)) {
            throw new \InvalidArgumentException('Invalid RSVP status.');
        }

        $time = time();
        $existingId = $this->connection->fetchOne(
            'SELECT id FROM tl_psa_event_rsvp WHERE event_id = ? AND member_id = ?',
            [$eventId, $memberId],
        );

        if (\is_string($existingId) || is_int($existingId)) {
            $this->connection->executeStatement(
                'UPDATE tl_psa_event_rsvp SET status = ?, tstamp = ? WHERE id = ?',
                [$status, $time, (int) $existingId],
            );

            return;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_psa_event_rsvp (tstamp, event_id, member_id, status) VALUES (?, ?, ?, ?)',
            [$time, $eventId, $memberId, $status],
        );
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
