<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\Dbafs;
use Contao\FilesModel;

final class PsaMemberAvatarStorage
{
    private const UPLOAD_PATH = 'files/members/avatars';

    public static function ensureUploadFolder(): void
    {
        $projectDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        $absolute = $projectDir.'/'.self::UPLOAD_PATH;

        if (!is_dir($absolute)) {
            mkdir($absolute, 0775, true);
        }

        if (FilesModel::findByPath(self::UPLOAD_PATH) === null && Dbafs::shouldBeSynchronized(self::UPLOAD_PATH)) {
            Dbafs::addResource(self::UPLOAD_PATH);
        }

        self::ensurePublicSymlink();
    }

    private static function ensurePublicSymlink(): void
    {
        $projectDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        $sourceDir = $projectDir.'/files/members';
        $publicFilesDir = $projectDir.'/public/files';
        $publicLink = $publicFilesDir.'/members';

        if (!is_dir($sourceDir)) {
            return;
        }

        if (!is_dir($publicFilesDir)) {
            mkdir($publicFilesDir, 0775, true);
        }

        if (!file_exists($publicLink)) {
            symlink('../../files/members', $publicLink);
        }
    }

    public static function getUploadFolderUuid(): ?string
    {
        $file = FilesModel::findByPath(self::UPLOAD_PATH);

        if ($file === null) {
            return null;
        }

        return $file->uuid;
    }
}
