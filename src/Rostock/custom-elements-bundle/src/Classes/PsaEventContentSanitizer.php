<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

final class PsaEventContentSanitizer
{
  /**
   * Strip accidental full-page HTML (header/footer/main) pasted into event text.
   */
  public static function sanitize(?string $html): string
  {
    if ($html === null || $html === '') {
      return '';
    }

    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $patterns = [
      '#<footer\b[^>]*>.*?</footer>#is',
      '#<header\b[^>]*class=["\'][^"\']*psa-header[^"\']*["\'][^>]*>.*?</header>#is',
      '#<div[^>]*\bid=["\']footer["\'][^>]*>.*?</div>#is',
      '#<div[^>]*\bid=["\']header["\'][^>]*>.*?</div>#is',
      '#<main\b[^>]*>.*?</main>#is',
    ];

    foreach ($patterns as $pattern) {
      $html = preg_replace($pattern, '', $html) ?? $html;
    }

    return trim($html);
  }
}
