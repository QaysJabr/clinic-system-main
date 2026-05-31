<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply locale from session or default app locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['ar', 'en']);
        $locale = '';

        if ($request->hasSession()) {
            $locale = (string) $request->session()->get('locale', '');
        } elseif ($request->header('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($supportedLocales);
            if (is_string($preferred) && $preferred !== '') {
                $locale = $preferred;
            }
        }

        static $userLocaleColumnExists = null;

        if ($userLocaleColumnExists === null) {
            $userLocaleColumnExists = \Illuminate\Support\Facades\Schema::hasColumn('users', 'locale');
        }

        if ($locale === '' && $request->user() && $userLocaleColumnExists) {
            $savedLocale = (string) ($request->user()->locale ?? '');
            if ($savedLocale !== '') {
                $locale = $savedLocale;
                if ($request->hasSession()) {
                    $request->session()->put('locale', $savedLocale);
                }
            }
        }

        if ($locale === '') {
            $locale = (string) config('app.locale');
        }

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = (string) config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
