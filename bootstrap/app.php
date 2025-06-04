<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware'ler (tüm istekler için)
        $middleware->append(\App\Http\Middleware\LogRequestMiddleware::class);

        // Route middleware alias'ları
        $middleware->alias([
            'bearer.auth' => \App\Http\Middleware\BearerTokenMiddleware::class,
            'ip.blacklist' => \App\Http\Middleware\IpBlacklistMiddleware::class,
            'log.request' => \App\Http\Middleware\LogRequestMiddleware::class,
        ]);

        // API middleware group'una ekleme (opsiyonel)
        $middleware->group('api', [
            // Default API middlewares burada
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
