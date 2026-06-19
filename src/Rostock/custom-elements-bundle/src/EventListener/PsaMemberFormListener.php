<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Rostock\CustomElementsBundle\Classes\PsaMemberAvatarStorage;

#[AsHook('getAttributesFromDca')]
class PsaMemberFormListener
{
    public function __invoke(array $attributes, ?object $dca): array
    {
        $field = (string) ($attributes['name'] ?? '');

        return match ($field) {
            'university', 'familyInRostock' => $this->adjustCommunityField($attributes),
            'avatar' => $this->adjustAvatarField($attributes),
            default => $attributes,
        };
    }

    private function adjustCommunityField(array $attributes): array
    {
        $attributes['mandatory'] = false;

        return $attributes;
    }

    private function adjustAvatarField(array $attributes): array
    {
        $folder = PsaMemberAvatarStorage::getUploadFolderUuid();

        if ($folder !== null) {
            $attributes['storeFile'] = true;
            $attributes['uploadFolder'] = $folder;
            $attributes['doNotOverwrite'] = true;
        }

        $attributes['extensions'] = 'jpg,jpeg,png,webp';
        $attributes['mandatory'] = false;
        $attributes['blnSubmitInput'] = true;
        $attributes['maxImageWidth'] = 4000;
        $attributes['maxImageHeight'] = 4000;

        return $attributes;
    }
}
