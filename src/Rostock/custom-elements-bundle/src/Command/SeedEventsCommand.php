<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:seed-events',
    description: 'Reset sample PSA events, clean extras, and wire grid list + detail templates.',
)]
class SeedEventsCommand extends Command
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->framework->initialize();

        $calendar = CalendarModel::findOneBy('title', 'Events')
            ?? CalendarModel::findOneBy('title', 'PSA Events');

        if ($calendar === null) {
            $io->error('No events calendar found. Run psa:install-events first.');

            return Command::FAILURE;
        }

        $calendarId = (int) $calendar->id;
        $this->purgeEvents($io, $calendarId);
        $this->removeDuplicateCalendar($io, $calendarId);
        $this->removeUnusedLegacyModules($io);
        $this->seedSampleEvents($io, $calendarId);
        $this->wireModuleTemplates($io);

        $io->success('Sample events created. Open /events to review the grid.');

        return Command::SUCCESS;
    }

    private function purgeEvents(SymfonyStyle $io, int $keepCalendarId): void
    {
        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_calendar_events');

        $this->connection->executeStatement('DELETE FROM tl_psa_event_rsvp');
        $this->connection->executeStatement('DELETE FROM tl_calendar_events');

        if ($count > 0) {
            $io->writeln('Removed '.$count.' old event(s).');
        }
    }

    private function removeDuplicateCalendar(SymfonyStyle $io, int $keepCalendarId): void
    {
        $duplicate = CalendarModel::findOneBy('title', 'PSA Events');

        if ($duplicate !== null && (int) $duplicate->id !== $keepCalendarId) {
            $count = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM tl_calendar_events WHERE pid = ?',
                [$duplicate->id],
            );

            if ($count === 0) {
                $duplicate->delete();
                $io->writeln('Removed empty duplicate calendar PSA Events.');
            }
        }
    }

    private function removeUnusedLegacyModules(SymfonyStyle $io): void
    {
        foreach (['Event list', 'Event Reader'] as $name) {
            $module = ModuleModel::findOneBy('name', $name);

            if ($module === null) {
                continue;
            }

            $inUse = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM tl_content WHERE type = ? AND module = ?',
                ['module', $module->id],
            );

            if ($inUse === 0 && !\in_array((int) $module->id, [9, 10], true)) {
                $moduleId = (int) $module->id;
                $module->delete();
                $io->writeln('Removed unused legacy module '.$name.' (id '.$moduleId.').');
            }
        }
    }

    private function seedSampleEvents(SymfonyStyle $io, int $calendarId): void
    {
        $images = [
            $this->fileUuid('files/uploads/marek-piwnicki-aMUe_C9roy0-unsplash.jpg'),
            $this->fileUuid('files/uploads/tobias-rademacher-kq-Qf1ZCQvc-unsplash.jpg'),
            $this->fileUuid('files/uploads/marek-piwnicki-FUROBVGl87Y-unsplash.jpg'),
        ];

        $samples = [
            [
                'title' => 'Summer Community Picnic',
                'alias' => 'summer-community-picnic',
                'date' => '2026-07-12',
                'time' => '14:00',
                'endTime' => '18:00',
                'location' => 'Stadtpark Rostock',
                'address' => 'Schwanenteich, 18055 Rostock',
                'teaser' => '<p>Bring your family and friends for an afternoon of food, games, and connection by the lake. Everyone is welcome.</p>',
                'image' => $images[0],
            ],
            [
                'title' => 'Eid Celebration Dinner',
                'alias' => 'eid-celebration-dinner',
                'date' => '2026-06-28',
                'time' => '18:30',
                'endTime' => '22:00',
                'location' => 'Gemeinschaftsraum Neptun',
                'address' => 'August-Bebel-Str. 55, 18055 Rostock',
                'teaser' => '<p>Join PSA Rostock for a shared dinner to celebrate Eid together. Please RSVP so we can plan catering.</p>',
                'image' => $images[1],
            ],
            [
                'title' => 'Welcome New Students',
                'alias' => 'welcome-new-students',
                'date' => '2026-10-15',
                'time' => '17:00',
                'endTime' => '20:00',
                'location' => 'Universitätsplatz',
                'address' => '18055 Rostock',
                'teaser' => '<p>Meet fellow students, get tips on life in Rostock, and find your community from day one.</p>',
                'image' => $images[2],
            ],
            [
                'title' => 'Cricket Match & BBQ',
                'alias' => 'cricket-match-bbq',
                'date' => '2026-08-30',
                'time' => '11:00',
                'endTime' => '16:00',
                'location' => 'Sportplatz Reutershagen',
                'address' => '18069 Rostock',
                'teaser' => '<p>All skill levels welcome. We play a friendly match and finish with a BBQ. Bring sports shoes and good energy.</p>',
                'image' => $images[0],
            ],
        ];

        foreach ($samples as $index => $sample) {
            $startDate = strtotime($sample['date'].' 00:00:00');
            $endDate = $startDate;

            $event = new CalendarEventsModel();
            $event->pid = $calendarId;
            $event->sorting = ($index + 1) * 128;
            $event->tstamp = time();
            $event->title = $sample['title'];
            $event->alias = $sample['alias'];
            $event->author = 1;
            $event->startDate = $startDate;
            $event->endDate = $endDate;
            $event->startTime = $startDate;
            $event->endTime = $endDate;
            $event->addTime = '0';
            $event->location = $sample['location'];
            $event->address = $sample['address'];
            $event->teaser = $sample['teaser'].'<p><strong>'.($sample['time'] ?? '').' – '.($sample['endTime'] ?? '').'</strong></p>';
            $event->published = '1';
            $event->source = 'default';
            $event->addImage = $sample['image'] ? '1' : '';
            $event->singleSRC = $sample['image'];
            $event->alt = $sample['title'];
            $event->imageTitle = $sample['title'];
            $event->size = '';
            $event->floating = 'above';
            $event->fullsize = '0';
            $event->save();

            $io->writeln('Created event: '.$sample['title'].' (id '.$event->id.').');
        }
    }

    private function wireModuleTemplates(SymfonyStyle $io): void
    {
        $list = ModuleModel::findOneBy('name', 'PSA Event List');
        $reader = ModuleModel::findOneBy('name', 'PSA Event Reader');

        if ($list !== null) {
            $list->cal_template = 'event_list_psa';
            $list->customTpl = 'mod_eventlist_psa';
            $list->cal_noSpan = '1';
            $list->cal_format = 'next_all';
            $list->headline = serialize(['unit' => 'h1', 'value' => 'Upcoming events']);
            $list->tstamp = time();
            $list->save();
            $io->writeln('Updated PSA Event List templates.');
        }

        if ($reader !== null) {
            $reader->cal_template = 'event_full_psa';
            $reader->customTpl = 'mod_eventreader_psa';
            $reader->com_template = 'com_default_psa';
            $reader->headline = '';
            $reader->tstamp = time();
            $reader->save();
            $io->writeln('Updated PSA Event Reader templates.');
        }
    }

    private function fileUuid(string $path): ?string
    {
        $file = FilesModel::findByPath($path);

        return $file?->uuid ?: null;
    }
}
