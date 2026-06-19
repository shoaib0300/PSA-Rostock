<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

final class PsaMeetupJsonPresenter
{
    public function __construct(private readonly PsaMeetup $meetup)
    {
    }

    /**
     * @return array{
     *     joinStatus: string|null,
     *     joinCount: int,
     *     declineCount: int,
     *     joiners: list<string>
     * }
     */
    public function rsvp(int $meetupId, int $memberId): array
    {
        return [
            'joinStatus' => $this->meetup->getMemberJoinStatus($meetupId, $memberId),
            'joinCount' => $this->meetup->getJoinCount($meetupId),
            'declineCount' => $this->meetup->getDeclineCount($meetupId),
            'joiners' => $this->meetup->getJoinerNames($meetupId),
        ];
    }

    /**
     * @return array{
     *     pollVote: int|null,
     *     poll: array<string, mixed>
     * }
     */
    public function poll(int $meetupId, int $memberId): array
    {
        $meetup = $this->meetup->getPublishedMeetup($meetupId, $memberId);

        if ($meetup === null) {
            return [
                'pollVote' => null,
                'poll' => [
                    'question' => '',
                    'options' => [],
                    'totalVotes' => 0,
                ],
            ];
        }

        return [
            'pollVote' => $memberId > 0 ? $this->meetup->getMemberPollVote($meetupId, $memberId) : null,
            'poll' => is_array($meetup['poll'] ?? null) ? $meetup['poll'] : [
                'question' => '',
                'options' => [],
                'totalVotes' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function comment(int $commentId, int $memberId): ?array
    {
        return $this->meetup->getComment($commentId, $memberId);
    }

    /**
     * @return list<array{emoji: string, count: int, memberReacted: bool}>
     */
    public function commentReactions(int $commentId, int $memberId): array
    {
        return $this->meetup->getCommentReactions($commentId, $memberId);
    }
}
