<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendUser;

#[AsHook('loadDataContainer')]
class PsaMemberDcaListener
{
    public function __invoke(string $table): void
    {
        if ($table !== 'tl_member') {
            return;
        }

        $user = FrontendUser::getInstance();

        if ($user?->id) {
            return;
        }

        $callbacks = $GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'] ?? [];

        $GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'] = array_values(array_filter(
            $callbacks,
            static function ($callback): bool {
                if (\is_array($callback)) {
                    return ($callback[1] ?? '') !== 'updateAccount';
                }

                return true;
            },
        ));
    }
}
