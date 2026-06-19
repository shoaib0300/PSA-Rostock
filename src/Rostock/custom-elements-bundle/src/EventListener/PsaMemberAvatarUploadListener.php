<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\Module;
use Rostock\CustomElementsBundle\Classes\PsaMemberDcaCallbacks;

/**
 * FormUpload stores files on disk but does not persist the UUID on tl_member by default.
 */
#[AsHook('updatePersonalData')]
class PsaMemberAvatarUploadListener
{
    /**
     * @param array<string, mixed>  $arrSubmitted
     * @param array<string, mixed>  $arrFiles
     */
    public function __invoke(FrontendUser $user, array $arrSubmitted, Module $module, array $arrFiles): void
    {
        if (!isset($arrFiles['avatar']) || !\is_array($arrFiles['avatar'])) {
            return;
        }

        $upload = $arrFiles['avatar'];

        if (empty($upload['uploaded']) || empty($upload['uuid'])) {
            return;
        }

        $binary = PsaMemberDcaCallbacks::storeAvatarUuid($upload);
        $member = MemberModel::findById((int) $user->id);

        if ($member === null || $binary === null) {
            return;
        }

        if ($member->avatar === $binary) {
            return;
        }

        $member->avatar = $binary;
        $member->tstamp = time();
        $member->save();

        $user->avatar = $binary;
    }
}
