<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    public function test_resolves_youtube_watch_url(): void
    {
        $embed = VideoEmbed::resolveEmbedUrl(null, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1', $embed);
    }

    public function test_prefers_explicit_embed_url(): void
    {
        $url = 'https://www.youtube-nocookie.com/embed/abc123';

        $this->assertSame($url, VideoEmbed::resolveEmbedUrl($url, ''));
    }
}
