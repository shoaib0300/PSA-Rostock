<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\FilesModel;
use Doctrine\DBAL\Connection;

final class PsaTeam
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPublishedMembers(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_psa_team_member WHERE published = ? ORDER BY sorting ASC, name ASC',
            ['1'],
        );

        $members = [];

        foreach ($rows as $row) {
            $members[] = $this->presentMember($row);
        }

        return $members;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function presentMember(array $row): array
    {
        $photo = $this->resolvePhotoPath($row['photo'] ?? null);

        return [
            'id' => (int) $row['id'],
            'name' => trim((string) ($row['name'] ?? '')),
            'photo' => $photo,
            'email' => $this->nonEmptyString($row['email'] ?? ''),
            'phone' => $this->nonEmptyString($row['phone'] ?? ''),
            'position' => $this->nonEmptyString($row['position'] ?? ''),
            'degree' => $this->nonEmptyString($row['degree'] ?? ''),
            'university' => $this->nonEmptyString($row['university'] ?? ''),
        ];
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
