<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Doctrine\DBAL\Connection;
use Rostock\CustomElementsBundle\Models\PsaEventRsvpModel;

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

        $existing = PsaEventRsvpModel::findOneBy(
            ['event_id' => $eventId, 'member_id' => $memberId],
        );

        if ($existing !== null) {
            $existing->status = $status;
            $existing->tstamp = time();
            $existing->save();

            return;
        }

        $rsvp = new PsaEventRsvpModel();
        $rsvp->event_id = $eventId;
        $rsvp->member_id = $memberId;
        $rsvp->status = $status;
        $rsvp->tstamp = time();
        $rsvp->save();
    }
}
