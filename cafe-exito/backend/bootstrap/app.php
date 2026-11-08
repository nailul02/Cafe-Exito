<?php

use App\Exceptions\ApiRenderer;
use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureStationAccess;
use App\Http\Middleware\ForceJson;
use App\Http\Middleware\ValidateTableToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureActive::class,
            'permission' => EnsurePermission::class,
            'station' => EnsureStationAccess::class,
            'table.token' => ValidateTableToken::class,
        ]);
        // Seluruh respons API berupa JSON, lengkap dengan X-Request-Id.
        $middleware->api(prepend: [ForceJson::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tidak ada halaman HTML/redirect: semua galat dirender sebagai JSON seragam.
        $exceptions->shouldRenderJsonWhen(fn () => true);
        $exceptions->render(fn (Throwable $e) => ApiRenderer::render($e, request()));
    })->create();
