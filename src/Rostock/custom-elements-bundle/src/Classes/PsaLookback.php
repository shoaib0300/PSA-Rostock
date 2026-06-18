<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;

final class PsaLookback
{
    public function __construct(
        private readonly ContaoFramework $framework,
    ) {
    }

    /**
     * @return array{
     *     year: int,
     *     months: list<array<string, mixed>>,
     *     slides: list<array<string, mixed>>
     * }
     */
    public function build(int $calendarId, string $scope, ?int $year, string $eventBaseUrl): array
    {
        if ($calendarId <= 0) {
            return ['year' => (int) date('Y'), 'months' => [], 'slides' => []];
        }

        $this->framework->initialize();

        $rows = $this->fetchEvents($calendarId, $scope, $year);
        $months = [];
        $slides = [];
        $index = 1;

        foreach ($rows as $row) {
            $startDate = (int) ($row['startDate'] ?? 0);

            if ($startDate <= 0) {
                continue;
            }

            $eventYear = (int) date('Y', $startDate);
            $eventMonth = (int) date('n', $startDate);
            $monthKey = sprintf('%04d-%02d', $eventYear, $eventMonth);
            $monthLabel = $this->monthName($eventMonth);
            $captionMonth = $monthLabel.' ('.$eventYear.')';

            if (!isset($months[$monthKey])) {
                $months[$monthKey] = [
                    'key' => $monthKey,
                    'year' => $eventYear,
                    'month' => $eventMonth,
                    'label' => $monthLabel,
                    'count' => 0,
                    'firstIndex' => $index,
                ];
            }

            ++$months[$monthKey]['count'];

            $alias = trim((string) ($row['alias'] ?? ''));
            $href = ($eventBaseUrl !== '' && $alias !== '') ? $eventBaseUrl.'/'.$alias : '';

            $slides[] = [
                'id' => (int) ($row['id'] ?? 0),
                'index' => $index,
                'title' => trim((string) ($row['title'] ?? '')),
                'monthKey' => $monthKey,
                'monthLabel' => $captionMonth,
                'image' => $this->resolveImagePath($row),
                'href' => $href,
            ];

            ++$index;
        }

        $monthList = array_values($months);
        $displayYear = $year ?? ($monthList[0]['year'] ?? (int) date('Y'));

        return [
            'year' => $displayYear,
            'months' => $monthList,
            'slides' => $slides,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchEvents(int $calendarId, string $scope, ?int $year): array
    {
        $collection = CalendarEventsModel::findBy('pid', $calendarId);

        if ($collection === null) {
            return [];
        }

        $today = strtotime('today');
        $rows = [];

        foreach ($collection as $event) {
            if ((string) $event->published !== '1') {
                continue;
            }

            $startDate = (int) $event->startDate;

            if ($startDate <= 0) {
                continue;
            }

            if ($scope === 'past' && $startDate >= $today) {
                continue;
            }

            if ($scope === 'upcoming' && $startDate < $today) {
                continue;
            }

            if ($year !== null) {
                $eventYear = (int) date('Y', $startDate);

                if ($eventYear !== $year) {
                    continue;
                }
            }

            $rows[] = [
                'id' => (int) $event->id,
                'title' => (string) $event->title,
                'alias' => (string) $event->alias,
                'startDate' => $startDate,
                'addImage' => (string) $event->addImage,
                'singleSRC' => $event->singleSRC,
            ];
        }

        if ($scope === 'upcoming') {
            usort($rows, static fn (array $a, array $b): int => $a['startDate'] <=> $b['startDate']);
        } else {
            usort($rows, static fn (array $a, array $b): int => $b['startDate'] <=> $a['startDate']);
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveImagePath(array $row): ?string
    {
        if (($row['addImage'] ?? '') !== '1') {
            return null;
        }

        $value = $row['singleSRC'] ?? null;

        if (!\is_string($value) || $value === '') {
            return null;
        }

        $file = FilesModel::findByUuid($value);

        if ($file === null || $file->path === '') {
            return null;
        }

        return '/'.ltrim((string) $file->path, '/');
    }

    private function monthName(int $month): string
    {
        $labels = $GLOBALS['TL_LANG']['MONTHS'] ?? null;

        if (\is_array($labels) && isset($labels[$month]) && \is_string($labels[$month])) {
            return $labels[$month];
        }

        return date('F', mktime(0, 0, 0, $month, 1));
    }
}
