<?php

namespace App\Support;

final class VideoEmbed
{
    /**
     * يحوّل رابط مشاهدة YouTube/Vimeo إلى رابط embed آمن للـ iframe.
     */
    public static function resolveEmbedUrl(?string $embedUrl, ?string $watchUrl): ?string
    {
        $embed = trim((string) $embedUrl);
        if ($embed !== '' && self::isAllowedHost($embed)) {
            return $embed;
        }

        $watch = trim((string) $watchUrl);
        if ($watch === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{6,})~', $watch, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0&modestbranding=1';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $watch, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        if (self::isAllowedHost($watch) && str_contains($watch, '/embed')) {
            return $watch;
        }

        return null;
    }

    private static function isAllowedHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return false;
        }

        $host = strtolower($host);

        return in_array($host, [
            'www.youtube.com',
            'youtube.com',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
            'player.vimeo.com',
            'vimeo.com',
        ], true);
    }
}
