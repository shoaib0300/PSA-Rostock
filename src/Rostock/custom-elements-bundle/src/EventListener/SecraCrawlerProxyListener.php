<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 512)]
class SecraCrawlerProxyListener
{
    private const AUTH = 'kjmnd7893n';

    private const RENDERER_URL = 'http://www.optimale-praesentation.de/frontend/renderer/webcrawler.php';

    private const SECRA_HOST = 'www.rostock.de';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->shouldProxy($request)) {
            return;
        }

        $event->setResponse($this->fetchPrerenderedHtml($request));
    }

    private function shouldProxy(Request $request): bool
    {
        if ($request->getPathInfo() === '/simpleproxy.php') {
            return $request->query->get('auth') === self::AUTH;
        }

        $queryString = $request->getQueryString() ?? '';
        $userAgent = strtolower($request->headers->get('User-Agent', ''));

        return str_contains($queryString, '_escaped_fragment_=')
            || (str_contains($queryString, '_fb_share_=') && str_contains($userAgent, 'face'));
    }

    private function fetchPrerenderedHtml(Request $request): Response
    {
        $query = $request->query->all();

        if ($request->getPathInfo() !== '/simpleproxy.php') {
            $query['auth'] = self::AUTH;
            $query['secrahost'] = self::SECRA_HOST;
            $query['secrapath'] = $request->getPathInfo();
        }

        $query['secrahost'] = self::SECRA_HOST;
        $query['secraproxyproto'] = $request->isSecure() ? 'https' : 'http';

        try {
            $response = $this->httpClient->request('GET', self::RENDERER_URL, [
                'query' => $query,
                'headers' => [
                    'X-Forwarded-For' => $request->getClientIp() ?? '',
                    'Accept-Encoding' => 'identity',
                ],
                'timeout' => 60,
            ]);

            $content = $response->getContent(false);

            if ($response->getStatusCode() !== 200 || $content === '') {
                return new Response('', Response::HTTP_BAD_GATEWAY);
            }
        } catch (\Throwable) {
            return new Response('', Response::HTTP_BAD_GATEWAY);
        }

        if (preg_match('/(<html.*?<\/html>)/s', $content, $matches)) {
            $content = $matches[1];
        }

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }
}
