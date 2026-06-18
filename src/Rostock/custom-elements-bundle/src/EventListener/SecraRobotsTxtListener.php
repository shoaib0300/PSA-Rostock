<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\RobotsTxtEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use webignition\RobotsTxt\Directive\Directive;

#[AsEventListener(event: ContaoCoreEvents::ROBOTS_TXT)]
class SecraRobotsTxtListener
{
    public function __invoke(RobotsTxtEvent $event): void
    {
        $rootPage = $event->getRootPage();
        $scheme = $rootPage->useSSL ? 'https://' : 'http://';
        $host = $rootPage->dns ?: $event->getRequest()->server->get('HTTP_HOST');

        $event->getFile()->getNonGroupDirectives()->add(
            new Directive('Sitemap', $scheme.$host.'/ro_object_sitemap.xml')
        );
    }
}
