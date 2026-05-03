<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            // \App\Http\Middleware\CheckRole::class,
        ]);
        $middleware->appendToPriorityList(ConvertEmptyStringsToNull::class, SecurityHeaders::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'session.validate' => \App\Http\Middleware\ValidateSession::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();
