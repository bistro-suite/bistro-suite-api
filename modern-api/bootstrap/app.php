<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\BlockDemoAdminWrites;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        then: function (): void {
            Illuminate\Support\Facades\Route::get('/health', fn () => response()->json([
                'status' => 'ok',
                'service' => 'bistro-suite-api',
            ]));
        },
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias(['admin.demo_read_only' => BlockDemoAdminWrites::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep framework defaults until product-specific error reporting is selected.
    })
    ->create();
