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
        $middleware->web(append: [
            // Conditionally disable VerifyCsrfToken for testing environment
            \App\Http\Middleware\VerifyCsrfToken::class => env('APP_ENV') === 'testing' ? null : \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->alias([
            'admin.token' => \App\Http\Middleware\AdminToken::class,
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'Image' => \Intervention\Image\Facades\Image::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
