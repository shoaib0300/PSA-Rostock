<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'psa_hero', category: 'PSA Rostock', template: 'psa_hero')]
class PsaHeroController extends AbstractContentElementController
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm'];

    private const DEFAULT_SCROLL_ITEMS = [
        ['title' => '', 'text' => 'Connect with fellow Pakistanis living in Rostock through cultural events, meetups, and community support.'],
        ['title' => '', 'text' => 'Discover upcoming gatherings, register for membership, and stay part of a growing network built on belonging.'],
        ['title' => '', 'text' => 'From team initiatives to contributor programs — help shape the community you want to be part of.'],
    ];

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request)) {
            $GLOBALS['PSA_HAS_HERO'] = true;
            $GLOBALS['TL_CSS']['psa_hero'] = 'bundles/customelements/frontend/css/psa_hero.css';
            $GLOBALS['TL_BODY']['psa_hero'] = FrontendTemplate::generateScriptTag(
                'bundles/customelements/frontend/js/psa_hero.js',
                false,
                null
            );

            $headline = StringUtil::deserialize($model->headline, true);

            $template->set('headline', $headline['value'] ?? '');
            $template->set('hl', $headline['unit'] ?? 'h1');
            $template->set('lede', trim((string) ($model->subline ?? '')));
            $template->set('addButton', (bool) $model->addButton);
            $template->set('button_label', (string) ($model->button_label ?? ''));
            $template->set('button_link', (string) ($model->button_link ?? ''));
            $template->set('button_target', (bool) $model->button_target);
            $template->set('scrollerLabel', $model->hero_caption ?: 'What we do');
            $template->set('scrollItems', $this->parseScrollItems($model->text));
            $template->set('slides', $this->buildSlides($model));
        } else {
            $template = new BackendTemplate('be_wildcard');
            $headline = StringUtil::deserialize($model->headline, true);
            $template->title = $headline['value'] ?? 'PSA Hero';
            $template->wildcard = '### PSA Hero ###';
        }

        return $template->getResponse();
    }

    /**
     * @return list<array{title: string, text: string}>
     */
    private function parseScrollItems(?string $text): array
    {
        if (!$text) {
            return self::DEFAULT_SCROLL_ITEMS;
        }

        if (preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>\s*<p[^>]*>(.*?)<\/p>/is', $text, $matches, PREG_SET_ORDER)) {
            $items = [];

            foreach ($matches as $match) {
                $title = trim(strip_tags($match[1] ?? ''));
                $body = trim(strip_tags($match[2] ?? ''));

                if ($title !== '' || $body !== '') {
                    $items[] = ['title' => $title, 'text' => $body];
                }
            }

            if ($items !== []) {
                return $items;
            }
        }

        if (str_contains($text, '</p>')) {
            preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $text, $matches);
            $items = [];

            foreach ($matches[1] ?? [] as $paragraph) {
                $body = trim(strip_tags($paragraph));

                if ($body !== '') {
                    $items[] = ['title' => '', 'text' => $body];
                }
            }

            if ($items !== []) {
                return $items;
            }
        }

        $plain = trim(strip_tags($text));

        if ($plain === '') {
            return self::DEFAULT_SCROLL_ITEMS;
        }

        $parts = preg_split('/\R\s*\R/', $plain) ?: [];
        $items = [];

        foreach (array_filter(array_map('trim', $parts)) as $part) {
            $items[] = ['title' => '', 'text' => $part];
        }

        return $items !== [] ? $items : [['title' => '', 'text' => $plain]];
    }

    /**
     * @return list<array{type: string, path: string, extension: string, alt: string}>
     */
    private function buildSlides(ContentModel $model): array
    {
        $slides = [];
        $uuids = StringUtil::deserialize($model->multiSRC, true) ?: [];

        foreach ($uuids as $uuid) {
            $file = FilesModel::findByUuid($uuid);

            if ($file === null) {
                continue;
            }

            $extension = strtolower(pathinfo($file->path, PATHINFO_EXTENSION));
            $slides[] = [
                'type' => \in_array($extension, self::VIDEO_EXTENSIONS, true) ? 'video' : 'image',
                'path' => $file->path,
                'extension' => $extension,
                'alt' => $file->name,
            ];
        }

        return $slides;
    }
}
