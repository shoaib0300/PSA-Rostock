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
