<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\FilesModel;
use Contao\Image\ResizeConfiguration;
use Contao\Image\ResizeOptions;
use Contao\System;

final class PsaMemberAvatarProcessor
{
    public const SIZE = 400;

    public static function normalize(string $uuid): void
    {
        if ($uuid === '') {
            return;
        }

        System::getContainer()->get('contao.framework')->initialize();

        $file = FilesModel::findByUuid($uuid);

        if ($file === null || (string) $file->path === '') {
            return;
        }

        $projectDir = (string) System::getContainer()->getParameter('kernel.project_dir');
        $absolutePath = $projectDir.'/'.ltrim((string) $file->path, '/');

        if (!is_file($absolutePath)) {
            return;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if (!\in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }

        /** @var ImageFactoryInterface $factory */
        $factory = System::getContainer()->get('contao.image.factory');

        $config = (new ResizeConfiguration())
            ->setWidth(self::SIZE)
            ->setHeight(self::SIZE)
            ->setMode(ResizeConfiguration::MODE_CROP);

        $tempPath = $absolutePath.'.psa-avatar.tmp';

        $options = (new ResizeOptions())
            ->setTargetPath($tempPath)
            ->setImagineOptions([
                'jpeg_quality' => 88,
                'webp_quality' => 88,
                'png_compression_level' => 8,
            ]);

        try {
            $factory->create($absolutePath, $config, $options);

            if (!is_file($tempPath)) {
                return;
            }

            rename($tempPath, $absolutePath);

            $image = $factory->create($absolutePath);
            $size = $image->getDimensions()->getSize();

            $file->width = $size->getWidth();
            $file->height = $size->getHeight();
            $file->tstamp = time();
            $file->save();
        } catch (\Throwable) {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
