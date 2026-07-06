<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'api/telegram/webhook/*',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->preventRequestsDuringMaintenance(except: [
            'login',
            'logout',
            'dashboard',
            'dashboard/master/broadcast',
            'terminal',
            'terminal/*',
            'settings',
            'settings/*',
            'users',
            'users/*',
            'branding',
            'profile',
            'evaluasi*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
