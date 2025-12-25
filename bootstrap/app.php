<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SetLocale; // 👈 add this

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Enable Sanctum SPA mode (sessions + CSRF for /api)
        $middleware->statefulApi();

        // aliases if you ever need them on routes
        $middleware->alias([
            'ensure.role' => EnsureRole::class,
            'locale'      => SetLocale::class, // optional alias
        ]);

        // ✅ Run SetLocale on every web request (all Blade pages)
        $middleware->appendToGroup('web', [
            SetLocale::class,
        ]);

        // If you also want localization on API JSON responses, you can add:
        // $middleware->appendToGroup('api', [ SetLocale::class ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
