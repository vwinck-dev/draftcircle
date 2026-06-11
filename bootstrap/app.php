<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureUserHasRole::class]);
        $middleware->encryptCookies(except: [
            'library_cookie_consent',
            'library_recommendation_interests',
        ]);
        $middleware->web(append: [
            SetLocale::class,
            App\Http\Middleware\HandleInertiaRequests::class,
            Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
