<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Throwable $e) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            \Illuminate\Support\Facades\Log::error('Exception caught: ' . $e->getMessage(), [
                'status' => $status,
                'exception' => $e,
                'url' => request()->url(),
            ]);

            if (config('app.debug')) {
                return null;
            }

            if (in_array($status, [404, 500])) {
                try {
                    return response()->view('errors.' . $status, [], $status);
                } catch (\Throwable $viewError) {
                    return response("Error {$status}", $status);
                }
            }
        });
    })->create();
