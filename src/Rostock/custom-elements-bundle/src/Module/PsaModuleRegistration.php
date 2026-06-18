<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Module;

use Contao\CoreBundle\Event\MemberActivationMailEvent;
use Contao\Environment;
use Contao\Idna;
use Contao\MemberModel;
use Contao\ModuleRegistration;
use Contao\System;

/**
 * PSA registration: 1-hour activation links and skip invalid admin notifications.
 */
class PsaModuleRegistration extends ModuleRegistration
{
    private const ACTIVATION_LINK_TTL = '+1 hour';

    protected function sendActivationMail($arrData): void
    {
        $container = System::getContainer();
        $optIn = $container->get('contao.opt_in');
        $removeOn = new \DateTime(self::ACTIVATION_LINK_TTL);

        $optInToken = $optIn->create('reg', $arrData['email'], ['tl_member' => [$arrData['id']]], $removeOn);

        $arrTokenData = $arrData;
        $arrTokenData['activation'] = $optInToken->getIdentifier();
        $arrTokenData['domain'] = Idna::decode(Environment::get('host'));
        $arrTokenData['link'] = Idna::decode(Environment::get('url')).Environment::get('requestUri').(str_contains(Environment::get('requestUri'), '?') ? '&' : '?').'token='.$optInToken->getIdentifier();

        $event = new MemberActivationMailEvent(
            MemberModel::findById($arrData['id']),
            $optInToken,
            \sprintf($GLOBALS['TL_LANG']['MSC']['emailSubject'], Idna::decode(Environment::get('host'))),
            $this->reg_text,
            $arrTokenData,
        );

        $container->get('event_dispatcher')->dispatch($event);

        if ($event->shouldSendOptInToken()) {
            $text = $container->get('contao.string.simple_token_parser')->parse($event->getText(), $event->getSimpleTokens());
            $optInToken->send($event->getSubject(), $text);
        }
    }

    protected function sendAdminNotification($intId, $arrData): void
    {
        $adminEmail = trim((string) ($GLOBALS['TL_ADMIN_EMAIL'] ?? ''));

        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        parent::sendAdminNotification($intId, $arrData);
    }
}
