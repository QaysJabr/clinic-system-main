<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * Open Graph image: static PNG, GD-generated PNG, or branded SVG fallback.
 */
final class OgImageController extends Controller
{
    public function __invoke(): Response
    {
        $static = public_path('images/og-default.png');
        if (File::exists($static)) {
            return response(File::get($static), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
            return $this->pngFromGd();
        }

        return $this->svgFallback();
    }

    private function pngFromGd(): Response
    {
        $w = 1200;
        $h = 630;
        $im = imagecreatetruecolor($w, $h);
        $brand = imagecolorallocate($im, 15, 76, 129);
        $accent = imagecolorallocate($im, 31, 122, 140);
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocate($im, 186, 230, 253);

        imagefilledrectangle($im, 0, 0, $w, $h, $brand);
        imagefilledrectangle($im, 0, (int) ($h * 0.55), $w, $h, $accent);

        $name = (string) config('app.name', 'Clinic');
        $tag = 'Clinic management platform';
        imagestring($im, 5, 64, 220, strlen($name) > 40 ? substr($name, 0, 37).'...' : $name, $white);
        imagestring($im, 3, 64, 270, strlen($tag) > 80 ? substr($tag, 0, 77).'...' : $tag, $muted);

        ob_start();
        imagepng($im, null, 6);
        $binary = ob_get_clean();
        imagedestroy($im);

        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function svgFallback(): Response
    {
        $name = e((string) config('app.name', 'Clinic System'));
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0F4C81"/>
      <stop offset="100%" stop-color="#0c3d66"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bg)"/>
  <rect x="0" y="360" width="1200" height="270" fill="#1F7A8C" opacity="0.35"/>
  <circle cx="980" cy="120" r="180" fill="#3B82F6" opacity="0.15"/>
  <rect x="64" y="200" width="72" height="72" rx="18" fill="#ffffff" opacity="0.15"/>
  <text x="100" y="248" text-anchor="middle" font-family="Arial,sans-serif" font-size="36" font-weight="700" fill="#ffffff">+</text>
  <text x="160" y="235" font-family="Arial,sans-serif" font-size="42" font-weight="700" fill="#ffffff">{$name}</text>
  <text x="160" y="285" font-family="Arial,sans-serif" font-size="22" fill="#BAE6FD">Clinic management platform</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
