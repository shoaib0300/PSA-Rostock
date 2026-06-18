<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Module;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Idna;
use Contao\ModuleLostPassword;
use Contao\PageModel;
use Contao\System;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;

class PsaModuleLostPassword extends ModuleLostPassword
{
    protected function setNewPassword(): void
    {
        $GLOBALS['TL_DCA']['tl_member']['fields']['password']['eval']['confirm'] = true;
        $this->strTemplate = 'mod_password';
        $this->Template = new FrontendTemplate($this->strTemplate);

        parent::setNewPassword();
    }

    protected function sendPasswordLink($objMember): void
    {
        $container = System::getContainer();
        $factory = $container->get('contao.rate_limit.member_password_factory');
        $limiter = $factory->create($objMember->id);

        if (!$limiter->consume()->isAccepted()) {
            $this->strTemplate = 'mod_message';
            $this->Template = new FrontendTemplate($this->strTemplate);
            $this->Template->type = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['tooManyPasswordResetAttempts'];

            return;
        }

        $optIn = $container->get('contao.opt_in');
        $optInToken = $optIn->create('pw', $objMember->email, ['tl_member' => [$objMember->id]]);

        $arrData = $objMember->row();
        $arrData['activation'] = $optInToken->getIdentifier();
        $arrData['domain'] = Idna::decode(Environment::get('host'));
        $arrData['link'] = Idna::decode(Environment::get('url')).Environment::get('requestUri').(str_contains(Environment::get('requestUri'), '?') ? '&' : '?').'token='.$optInToken->getIdentifier();

        $optInToken->send(
            \sprintf($GLOBALS['TL_LANG']['MSC']['passwordSubject'], Idna::decode(Environment::get('host'))),
            $container->get('contao.string.simple_token_parser')->parse($this->reg_password, $arrData),
        );

        $container->get('monolog.logger.contao.access')->info('A new password has been requested for user ID '.$objMember->id.' ('.Idna::decodeEmail($objMember->email).')');

        $container->get(PsaMemberFlash::class)->set(PsaMemberFlash::TYPE_PASSWORD_RESET_SENT);

        if ($objJumpTo = PageModel::findById($this->objModel->jumpTo)) {
            $this->jumpToOrReload($objJumpTo->row());
        }

        $this->reload();
    }
}
