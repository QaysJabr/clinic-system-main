<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.csp.enabled', true)) {
            return $next($request);
        }

        $viteDev = config('app.debug') && config('security.csp.allow_vite_dev', true);
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        /*
         * In local Vite mode script-src uses 'unsafe-inline' without a nonce.
         * Inline scripts tagged with a nonce are blocked unless that nonce is in script-src.
         */
        if (! $viteDev) {
            View::share('cspNonce', $nonce);
            Vite::useCspNonce($nonce);
        } else {
            View::share('cspNonce', null);
        }

        $response = $next($request);

        $directives = $this->buildCsp($nonce, $request);

        $header = config('security.csp.report_only', false)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($header, $directives);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function buildCsp(string $nonce, Request $request): string
    {
        $viteDev = config('app.debug') && config('security.csp.allow_vite_dev', true);
        $viteHosts = ['http://127.0.0.1:5173', 'http://[::1]:5173'];
        $viteWsHosts = ['ws://127.0.0.1:5173', 'ws://[::1]:5173'];
        $allowEval = (bool) config('security.csp.allow_unsafe_eval', true);

        $fontStyleHosts = 'https://fonts.googleapis.com';
        $fontSrc = ["'self'", 'https://fonts.gstatic.com', 'data:'];

        if ($viteDev) {
            /*
             * Local Vite HMR injects inline scripts/styles without nonces.
             * Do not use nonce in script-src/style-src-elem here — 'unsafe-inline' is ignored when a nonce is present.
             */
            $scriptSrc = ["'self'", "'unsafe-inline'"];
            if ($allowEval) {
                $scriptSrc[] = "'unsafe-eval'";
            }
            array_push($scriptSrc, ...$viteHosts);

            $styleElem = array_merge(["'self'", "'unsafe-inline'", $fontStyleHosts], $viteHosts);
            $styleAttr = ["'unsafe-inline'"];

            $connectSrc = array_merge(["'self'"], $viteHosts, $viteWsHosts);
            array_push($fontSrc, ...$viteHosts);
        } else {
            $scriptSrc = ["'self'", "'nonce-{$nonce}'"];
            if ($allowEval) {
                $scriptSrc[] = "'unsafe-eval'";
            }

            $styleElem = ["'self'", "'nonce-{$nonce}'", $fontStyleHosts];
            $styleAttr = ["'unsafe-inline'"];
            $connectSrc = ["'self'"];
        }

        $parts = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "img-src 'self' data: https:",
            'font-src '.implode(' ', $fontSrc),
            'script-src '.implode(' ', $scriptSrc),
            'style-src-elem '.implode(' ', $styleElem),
            'style-src-attr '.implode(' ', $styleAttr),
            'connect-src '.implode(' ', $connectSrc),
        ];

        return implode('; ', $parts);
    }
}
