<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class TranslateHtmlResponse
{
    /**
     * Translate static Arabic UI text in rendered HTML.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->ajax() || $request->headers->has('X-Locale-Apply-Subrequest')) {
            return $response;
        }

        $sessionLocale = (string) $request->session()->get('locale', '');
        if (App::getLocale() !== 'en' || ($sessionLocale !== '' && $sessionLocale !== 'en')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return $response;
        }

        if (! preg_match('/\p{Arabic}/u', $content)) {
            return $response;
        }

        $translationMap = $this->jsonFallbackMap('en');
        if ($translationMap === []) {
            return $response;
        }

        $translatedContent = $this->translateHtmlToEnglish($content, $translationMap);
        if ($translatedContent !== null && $translatedContent !== '') {
            $response->setContent($translatedContent);
        }

        return $response;
    }

    private function translateHtmlToEnglish(string $html, array $translationMap): ?string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()');
        if ($textNodes !== false) {
            foreach ($textNodes as $node) {
                $parent = strtolower($node->parentNode?->nodeName ?? '');
                if (in_array($parent, ['script', 'style', 'noscript'], true)) {
                    continue;
                }

                $original = $node->nodeValue ?? '';
                $translated = $this->translateChunk($original, $translationMap);
                if ($translated !== $original) {
                    $node->nodeValue = $translated;
                }
            }
        }

        $elements = $xpath->query('//*[@placeholder or @title or @aria-label or (@value and (self::input or self::button))]');
        if ($elements !== false) {
            foreach ($elements as $element) {
                foreach (['placeholder', 'title', 'aria-label', 'value'] as $attribute) {
                    if (! $element->hasAttribute($attribute)) {
                        continue;
                    }

                    $original = $element->getAttribute($attribute);
                    $translated = $this->translateChunk($original, $translationMap);
                    if ($translated !== $original) {
                        $element->setAttribute($attribute, $translated);
                    }
                }
            }
        }

        $output = $dom->saveHTML() ?: null;
        if ($output === null) {
            return null;
        }

        return preg_replace('/^<\?xml[^>]+>\s*/', '', $output) ?? $output;
    }

    private function translateChunk(string $text, array $translationMap): string
    {
        if ($text === '' || ! preg_match('/\p{Arabic}/u', $text)) {
            return $text;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return $text;
        }

        if (array_key_exists($trimmed, $translationMap)) {
            return str_replace($trimmed, $translationMap[$trimmed], $text);
        }

        return $text;
    }

    private function jsonFallbackMap(string $locale): array
    {
        $localeJsonPath = lang_path($locale.'.json');
        if (! is_file($localeJsonPath)) {
            return [];
        }

        $translations = json_decode((string) file_get_contents($localeJsonPath), true);
        if (! is_array($translations)) {
            return [];
        }

        return $translations;
    }
}
