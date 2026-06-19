<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\Dbafs;
use Contao\FilesModel;

final class PsaSeoAssetStorage
{
    public const ASSET_PATH = 'files/favicons';

    public static function ensureFolder(): string
    {
        $projectDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        $absolute = $projectDir.'/'.self::ASSET_PATH;

        if (!is_dir($absolute)) {
            mkdir($absolute, 0775, true);
        }

        if (FilesModel::findByPath(self::ASSET_PATH) === null && Dbafs::shouldBeSynchronized(self::ASSET_PATH)) {
            Dbafs::addResource(self::ASSET_PATH);
        }

        self::ensurePublicSymlink($projectDir);

        return $absolute;
    }

    private static function ensurePublicSymlink(string $projectDir): void
    {
        $sourceDir = $projectDir.'/files/favicons';
        $publicFilesDir = $projectDir.'/public/files';
        $publicLink = $publicFilesDir.'/favicons';

        if (!is_dir($sourceDir)) {
            return;
        }

        if (!is_dir($publicFilesDir)) {
            mkdir($publicFilesDir, 0775, true);
        }

        if (!file_exists($publicLink)) {
            symlink('../../files/favicons', $publicLink);
        }
    }
}
