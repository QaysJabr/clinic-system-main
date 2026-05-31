<?php

use App\Http\Controllers\HealthController;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureEmailVerifiedWhenEnabled;
use App\Http\Middleware\EnsurePlatformOwner;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Http\Middleware\EnsureUserHasClinic;
use App\Http\Middleware\PreventPlatformOwnerFromClinic;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackActiveSession;
use App\Http\Middleware\TranslateHtmlResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/health', HealthController::class)
                ->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            SetLocale::class,
            TranslateHtmlResponse::class,
            TrackActiveSession::class,
        ]);

        $middleware->api(append: [
            SetLocale::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureTwoFactorVerified::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);

        $middleware->alias([
            'verified' => EnsureEmailVerifiedWhenEnabled::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'subscription.active' => CheckSubscription::class,
            'check.subscription' => CheckSubscription::class,
            'platform.owner' => EnsurePlatformOwner::class,
            'prevent.platform.owner.from.clinic' => PreventPlatformOwnerFromClinic::class,
            'ensure.user.has.clinic' => EnsureUserHasClinic::class,
            'api.clinic' => \App\Http\Middleware\EnsureApiClinicUser::class,
            'api.platform' => \App\Http\Middleware\EnsureApiPlatformOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (class_exists(Integration::class) && config('sentry.dsn')) {
            Integration::handles($exceptions);
        }
    })->create();
