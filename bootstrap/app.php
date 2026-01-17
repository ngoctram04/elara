<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\CheckUserActive;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | 🔥 GLOBAL WEB MIDDLEWARE
        | (khóa là bấm đâu cũng bị văng)
        |--------------------------------------------------------------------------
        */
        $middleware->web(append: [
            CheckUserActive::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Alias middleware
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'is_admin'     => IsAdmin::class,
            'check_active' => CheckUserActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();