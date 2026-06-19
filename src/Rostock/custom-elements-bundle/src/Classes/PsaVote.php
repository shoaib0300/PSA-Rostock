<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\Date;
use Contao\FilesModel;
use Doctrine\DBAL\Connection;

final class PsaVote
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getActiveCampaigns(int $memberId = 0): array
    {
        $now = time();
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_psa_vote_campaign
             WHERE published = ?
             AND (startDate = 0 OR startDate <= ?)
             AND (endDate = 0 OR endDate >= ?)
             ORDER BY startDate DESC, title ASC',
            ['1', $now, $now],
        );

        $campaigns = [];

        foreach ($rows as $row) {
            $campaign = $this->presentCampaign($row, $memberId);

            if ($campaign['status'] === 'active') {
                $campaigns[] = $campaign;
            }
        }

        return $campaigns;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getVisibleCampaigns(int $memberId = 0): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_psa_vote_campaign
             WHERE published = ?
             ORDER BY startDate DESC, title ASC',
            ['1'],
        );

        $campaigns = [];

        foreach ($rows as $row) {
            $campaigns[] = $this->presentCampaign($row, $memberId);
        }

        return array_values(array_filter(
            $campaigns,
            static fn (array $campaign): bool => \in_array($campaign['status'], ['active', 'upcoming', 'ended'], true),
        ));
    }

    public function getCampaign(int $campaignId, int $memberId = 0): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM tl_psa_vote_campaign WHERE id = ? AND published = ?',
            [$campaignId, '1'],
        );

        if ($row === false) {
            return null;
        }

        return $this->presentCampaign($row, $memberId);
    }

    /**
     * @param array<int, int> $selections reason_id => candidate_id
     */
    public function submitBallot(int $campaignId, int $memberId, array $selections): void
    {
        if ($memberId <= 0) {
            throw new \InvalidArgumentException('Login required.');
        }

        $campaign = $this->getCampaign($campaignId);

        if ($campaign === null) {
            throw new \InvalidArgumentException('Invalid campaign.');
        }

        if ($campaign['status'] !== 'active') {
            throw new \InvalidArgumentException('Campaign is not open for voting.');
        }

        $positions = $campaign['positions'] ?? [];

        if ($positions === []) {
            throw new \InvalidArgumentException('No candidates configured.');
        }

        $requiredReasonIds = [];

        foreach ($positions as $position) {
            $reasonKey = (int) $position['reasonKey'];
            $requiredReasonIds[$reasonKey] = true;

            if (!isset($selections[$reasonKey]) && \count($position['candidates'] ?? []) === 1) {
                $selections[$reasonKey] = (int) $position['candidates'][0]['id'];
            }
        }

        if (\count($selections) !== \count($requiredReasonIds)) {
            throw new \InvalidArgumentException($GLOBALS['TL_LANG']['PSA']['vote_incomplete'] ?? 'Please select a candidate for each position.');
        }

        $time = time();

        foreach ($selections as $reasonKey => $candidateId) {
            $reasonKey = (int) $reasonKey;
            $candidateId = (int) $candidateId;

            if (!isset($requiredReasonIds[$reasonKey])) {
                throw new \InvalidArgumentException('Invalid position.');
            }

            $candidate = $this->connection->fetchAssociative(
                'SELECT * FROM tl_psa_vote_candidate WHERE id = ? AND pid = ? AND published = ?',
                [$candidateId, $campaignId, '1'],
            );

            if ($candidate === false) {
                throw new \InvalidArgumentException('Invalid candidate.');
            }

            if ($this->resolveReasonKey($candidate) !== $reasonKey) {
                throw new \InvalidArgumentException('Invalid candidate for position.');
            }

            $existingId = $this->connection->fetchOne(
                'SELECT id FROM tl_psa_vote_ballot WHERE campaign_id = ? AND reason_id = ? AND member_id = ?',
                [$campaignId, $reasonKey, $memberId],
            );

            if (\is_string($existingId) || is_int($existingId)) {
                $this->connection->executeStatement(
                    'UPDATE tl_psa_vote_ballot SET candidate_id = ?, tstamp = ? WHERE id = ?',
                    [$candidateId, $time, (int) $existingId],
                );

                continue;
            }

            $this->connection->executeStatement(
                'INSERT INTO tl_psa_vote_ballot (tstamp, campaign_id, reason_id, candidate_id, member_id)
                 VALUES (?, ?, ?, ?, ?)',
                [$time, $campaignId, $reasonKey, $candidateId, $memberId],
            );
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function presentCampaign(array $row, int $memberId): array
    {
        $id = (int) $row['id'];
        $status = $this->resolveStatus($row);
        $memberVotes = $memberId > 0 ? $this->getMemberVotes($id, $memberId) : [];
        $showResults = $this->shouldShowResults($row, $status, $memberVotes !== []);
        $positions = $this->getCampaignPositions($id, $showResults, $status);
        $candidateCount = 0;

        foreach ($positions as $position) {
            $candidateCount += \count($position['candidates'] ?? []);
        }

        return [
            'id' => $id,
            'title' => trim((string) ($row['title'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
            'startDate' => $this->normalizeStoredDate((int) ($row['startDate'] ?? 0)),
            'endDate' => $this->normalizeStoredDate((int) ($row['endDate'] ?? 0), true),
            'startDateFormatted' => $this->formatStoredDate((int) ($row['startDate'] ?? 0)),
            'endDateFormatted' => $this->formatStoredDate((int) ($row['endDate'] ?? 0)),
            'status' => $status,
            'canVote' => $status === 'active',
            'showResults' => $showResults,
            'showResultsMode' => (string) ($row['showResults'] ?? 'after_vote'),
            'memberVotes' => $memberVotes,
            'hasVoted' => $memberVotes !== [],
            'positions' => $positions,
            'positionCount' => \count($positions),
            'candidateCount' => $candidateCount,
            'totalVoters' => $this->countDistinctVoters($id),
            'totalBallots' => $this->countBallots($id),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getCampaignPositions(int $campaignId, bool $showResults, string $status = 'active'): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT c.*, r.title AS reason_title, r.description AS reason_description, r.photo AS reason_photo
             FROM tl_psa_vote_candidate c
             LEFT JOIN tl_psa_vote_reason r ON r.id = c.reason_id
             WHERE c.pid = ? AND c.published = ?
             ORDER BY c.sorting ASC, c.name ASC',
            [$campaignId, '1'],
        );

        $grouped = [];

        foreach ($rows as $row) {
            $reasonKey = $this->resolveReasonKey($row);
            $positionLabel = $this->resolvePositionLabel($row);

            if (!isset($grouped[$reasonKey])) {
                $reasonId = (int) ($row['reason_id'] ?? 0);
                $reasonMeta = $reasonId > 0 ? $this->loadReasonMeta($reasonId) : null;

                $grouped[$reasonKey] = [
                    'reasonKey' => $reasonKey,
                    'title' => $positionLabel,
                    'description' => $reasonMeta['description'] ?? $this->nonEmptyString($row['reason_description'] ?? ''),
                    'photo' => $reasonMeta['photo'] ?? $this->resolvePhotoPath($row['reason_photo'] ?? null),
                    'candidates' => [],
                ];
            }

            $candidateId = (int) $row['id'];
            $votes = $showResults ? $this->countCandidateVotes($campaignId, $candidateId) : 0;

            $grouped[$reasonKey]['candidates'][] = [
                'id' => $candidateId,
                'name' => trim((string) ($row['name'] ?? '')),
                'photo' => $this->resolvePhotoPath($row['photo'] ?? null) ?? $grouped[$reasonKey]['photo'],
                'position' => $positionLabel,
                'description' => $this->nonEmptyString($row['description'] ?? ''),
                'votes' => $votes,
            ];
        }

        if ($showResults) {
            $isFinal = $status === 'ended';

            foreach ($grouped as &$position) {
                $totalVotes = array_sum(array_column($position['candidates'], 'votes'));
                $maxVotes = 0;

                foreach ($position['candidates'] as $candidate) {
                    $maxVotes = max($maxVotes, (int) $candidate['votes']);
                }

                $topNames = [];

                foreach ($position['candidates'] as &$candidate) {
                    $candidate['percent'] = $totalVotes > 0
                        ? (int) round(($candidate['votes'] / $totalVotes) * 100)
                        : 0;
                    $isTop = $maxVotes > 0 && (int) $candidate['votes'] === $maxVotes;

                    if ($isFinal) {
                        $candidate['isWinner'] = $isTop;
                        $candidate['isLeading'] = false;
                    } else {
                        $candidate['isWinner'] = false;
                        $candidate['isLeading'] = $isTop;
                    }

                    if ($isTop) {
                        $topNames[] = $candidate['name'];
                    }
                }
                unset($candidate);

                if ($isFinal) {
                    $position['winnerNames'] = $topNames;
                    $position['hasWinner'] = $topNames !== [];
                    $position['leaderNames'] = [];
                    $position['hasLeader'] = false;
                } else {
                    $position['leaderNames'] = $topNames;
                    $position['hasLeader'] = $topNames !== [];
                    $position['winnerNames'] = [];
                    $position['hasWinner'] = false;
                }
            }
            unset($position);
        }

        return array_values($grouped);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveReasonKey(array $row): int
    {
        $reasonId = (int) ($row['reason_id'] ?? 0);

        if ($reasonId > 0) {
            return $reasonId;
        }

        $position = trim((string) ($row['position'] ?? ''));

        if ($position === '') {
            return 0;
        }

        return (int) sprintf('%u', crc32('custom:'.$position));
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolvePositionLabel(array $row): string
    {
        $reasonId = (int) ($row['reason_id'] ?? 0);

        if ($reasonId > 0) {
            $title = trim((string) ($row['reason_title'] ?? ''));

            if ($title === '') {
                $fetched = $this->connection->fetchOne(
                    'SELECT title FROM tl_psa_vote_reason WHERE id = ?',
                    [$reasonId],
                );
                $title = is_string($fetched) ? trim($fetched) : '';
            }

            if ($title !== '') {
                return $title;
            }
        }

        $custom = trim((string) ($row['position'] ?? ''));

        return $custom !== '' ? $custom : 'General';
    }

    /**
     * @return array{description: ?string, photo: ?string}|null
     */
    private function loadReasonMeta(int $reasonId): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT title, description, photo FROM tl_psa_vote_reason WHERE id = ?',
            [$reasonId],
        );

        if ($row === false) {
            return null;
        }

        return [
            'description' => $this->nonEmptyString($row['description'] ?? ''),
            'photo' => $this->resolvePhotoPath($row['photo'] ?? null),
        ];
    }

    /**
     * @return array<int, int> reasonKey => candidate_id
     */
    private function getMemberVotes(int $campaignId, int $memberId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT reason_id, candidate_id FROM tl_psa_vote_ballot WHERE campaign_id = ? AND member_id = ?',
            [$campaignId, $memberId],
        );

        $votes = [];

        foreach ($rows as $row) {
            $votes[(int) $row['reason_id']] = (int) $row['candidate_id'];
        }

        return $votes;
    }

    private function countCandidateVotes(int $campaignId, int $candidateId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_psa_vote_ballot WHERE campaign_id = ? AND candidate_id = ?',
            [$campaignId, $candidateId],
        );
    }

    private function countDistinctVoters(int $campaignId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(DISTINCT member_id) FROM tl_psa_vote_ballot WHERE campaign_id = ?',
            [$campaignId],
        );
    }

    private function countBallots(int $campaignId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_psa_vote_ballot WHERE campaign_id = ?',
            [$campaignId],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveStatus(array $row): string
    {
        if (($row['published'] ?? '') !== '1') {
            return 'draft';
        }

        $now = time();
        $start = $this->normalizeStoredDate((int) ($row['startDate'] ?? 0));
        $end = $this->normalizeStoredDate((int) ($row['endDate'] ?? 0), true);

        if ($start > 0 && $now < $start) {
            return 'upcoming';
        }

        if ($end > 0 && $now > $end) {
            return 'ended';
        }

        return 'active';
    }

    private function normalizeStoredDate(int $value, bool $endOfDay = false): int
    {
        if ($value <= 0) {
            return 0;
        }

        if ($value >= 20000101 && $value <= 29991231) {
            $value = (int) strtotime(sprintf(
                '%04d-%02d-%02d',
                intdiv($value, 10000),
                intdiv($value % 10000, 100),
                $value % 100,
            ).($endOfDay ? ' 23:59:59' : ' 00:00:00'));
        } elseif ($endOfDay) {
            $value = (int) strtotime(date('Y-m-d', $value).' 23:59:59');
        } else {
            $value = (int) strtotime(date('Y-m-d', $value).' 00:00:00');
        }

        return $value > 0 ? $value : 0;
    }

    private function formatStoredDate(int $value): string
    {
        $timestamp = $this->normalizeStoredDate($value);

        return $timestamp > 0 ? Date::parse('d.m.Y', $timestamp) : '';
    }

    /**
     * @param array<string, mixed> $row
     */
    private function shouldShowResults(array $row, string $status, bool $memberHasVoted): bool
    {
        $mode = (string) ($row['showResults'] ?? 'after_vote');

        return match ($mode) {
            'always' => true,
            'never' => false,
            'after_end' => $status === 'ended',
            default => $memberHasVoted || $status === 'ended',
        };
    }

    public function isTickerEnabled(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_config'])) {
            return true;
        }

        $value = $this->connection->fetchOne('SELECT showTicker FROM tl_psa_vote_config WHERE id = 1');

        if ($value === false) {
            return true;
        }

        return (string) $value === '1';
    }

    public function shouldShowTickerOnPage(int $pageId): bool
    {
        if ($pageId <= 0 || !$this->isTickerEnabled()) {
            return false;
        }

        $pageIds = $this->getTickerPageIds();

        if ($pageIds === []) {
            return true;
        }

        return $this->isPageOrAncestorSelected($pageId, $pageIds);
    }

    /**
     * @return list<int>
     */
    private function getTickerPageIds(): array
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_psa_vote_config'])) {
            return [];
        }

        try {
            $raw = $this->connection->fetchOne('SELECT tickerPages FROM tl_psa_vote_config WHERE id = 1');
        } catch (\Throwable) {
            return [];
        }

        if ($raw === false || $raw === null || $raw === '') {
            return [];
        }

        $ids = \Contao\StringUtil::deserialize($raw, true);

        if (!\is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($id): int => (int) $id, $ids)));
    }

    /**
     * @param list<int> $selectedPageIds
     */
    private function isPageOrAncestorSelected(int $pageId, array $selectedPageIds): bool
    {
        $page = \Contao\PageModel::findByPk($pageId);

        while ($page !== null) {
            if (\in_array((int) $page->id, $selectedPageIds, true)) {
                return true;
            }

            $pid = (int) $page->pid;

            if ($pid <= 0) {
                break;
            }

            $page = \Contao\PageModel::findByPk($pid);
        }

        return false;
    }

    /**
     * @return list<array{campaignId: int, campaign: string, position: string, names: string, kind: string}>
     */
    public function buildTickerItems(int $memberId = 0): array
    {
        $items = [];

        foreach ($this->getVisibleCampaigns($memberId) as $campaign) {
            if (empty($campaign['showResults'])) {
                continue;
            }

            $campaignId = (int) ($campaign['id'] ?? 0);
            $campaignTitle = trim((string) ($campaign['title'] ?? ''));
            $status = (string) ($campaign['status'] ?? '');
            $positions = is_array($campaign['positions'] ?? null) ? $campaign['positions'] : [];

            foreach ($positions as $position) {
                if ($status === 'ended' && !empty($position['hasWinner'])) {
                    $items[] = [
                        'campaignId' => $campaignId,
                        'campaign' => $campaignTitle,
                        'position' => trim((string) ($position['title'] ?? '')),
                        'names' => implode(', ', $position['winnerNames'] ?? []),
                        'kind' => 'winner',
                    ];
                } elseif ($status !== 'ended' && !empty($position['hasLeader'])) {
                    $items[] = [
                        'campaignId' => $campaignId,
                        'campaign' => $campaignTitle,
                        'position' => trim((string) ($position['title'] ?? '')),
                        'names' => implode(', ', $position['leaderNames'] ?? []),
                        'kind' => 'leading',
                    ];
                }
            }
        }

        return $items;
    }

    public function resolveVotePageUrl(): string
    {
        $pageId = $this->connection->fetchOne(
            "SELECT p.id FROM tl_page p
             INNER JOIN tl_article a ON a.pid = p.id
             INNER JOIN tl_content c ON c.pid = a.id AND c.ptable = 'tl_article' AND c.type = 'module'
             INNER JOIN tl_module m ON m.id = c.module AND m.type = 'psa_vote'
             WHERE p.published = '1'
             ORDER BY p.id ASC
             LIMIT 1",
        );

        if (!is_numeric($pageId)) {
            return '/vote';
        }

        $page = \Contao\PageModel::findByPk((int) $pageId);

        if ($page === null) {
            return '/vote';
        }

        return $page->getFrontendUrl();
    }

    private function nonEmptyString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    private function resolvePhotoPath(mixed $value): ?string
    {
        if (!\is_string($value) || $value === '') {
            return null;
        }

        $file = FilesModel::findByUuid($value);

        if ($file === null || $file->path === '') {
            return null;
        }

        return '/'.ltrim((string) $file->path, '/');
    }
}
