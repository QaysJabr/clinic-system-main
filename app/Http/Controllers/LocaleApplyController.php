<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Sets locale in session (and user profile) without re-dispatching the current page.
 * A full browser reload applies the new language everywhere and avoids session
 * conflicts from nested HTTP requests (which caused unexpected logouts).
 */
final class LocaleApplyController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $supportedLocales = config('app.supported_locales', ['ar', 'en']);

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($supportedLocales)],
            'path' => ['nullable', 'string', 'max:2048'],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        if ($request->user() && Schema::hasColumn('users', 'locale')) {
            /** @var \App\Models\User $user */
            $user = $request->user();
            $user->forceFill(['locale' => $locale])->save();
        }

        return response()->json([
            'ok' => true,
            'locale' => $locale,
            'reload' => true,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
