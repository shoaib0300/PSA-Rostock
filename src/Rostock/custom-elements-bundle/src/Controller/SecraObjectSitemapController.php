<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecraObjectSitemapController
{
    private const GENERATOR_URL = 'https://www.optimale-praesentation.de/frontend/seo/object_sitemap_generator.php';

    private const SECRATOID = 'f89000311';

    private const OBJECT_ROOT = 'https://www.rostock.de/ferienobjekte';

    private const HASH_BASE = '!m/3/object/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    #[Route('/op_object_sitemap.xml', defaults: ['_scope' => 'frontend'], methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $response = $this->httpClient->request('GET', self::GENERATOR_URL, [
                'query' => [
                    'secratoid' => self::SECRATOID,
                    'objectroot' => self::OBJECT_ROOT,
                    'hashbase' => self::HASH_BASE,
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode !== 200 || $content === '') {
                return new Response('SECRA sitemap unavailable', Response::HTTP_BAD_GATEWAY);
            }
        } catch (\Throwable) {
            return new Response('SECRA sitemap unavailable', Response::HTTP_BAD_GATEWAY);
        }

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
