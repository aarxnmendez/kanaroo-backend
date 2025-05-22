<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e) {
            // Only handle API requests
            if (request()->expectsJson() || request()->is('api/*')) {
                // Get status code
                $status = match (true) {
                    $e instanceof \Illuminate\Auth\AuthenticationException => 401,
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                    $e instanceof \Illuminate\Validation\ValidationException => 422,
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException => $e->getStatusCode(),
                    default => 500,
                };

                // Get message
                $message = match (true) {
                    $e instanceof \Illuminate\Validation\ValidationException => __('errors.invalid_data'),
                    $e instanceof \Illuminate\Auth\AuthenticationException => __('errors.unauthenticated'),
                    $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException => __('errors.unauthorized'),
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => __('errors.resource_not_found'),
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException => $e->getMessage() ?: __('errors.http_error'),
                    default => config('app.debug') ? $e->getMessage() : __('errors.server_error'),
                };

                // Build response data
                $data = [
                    'success' => false,
                    'message' => $message,
                    'status' => $status,
                ];

                // Add validation errors if applicable
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $data['errors'] = $e->errors();
                }

                // Add debug data when in debug mode
                if (config('app.debug')) {
                    $data['debug'] = [
                        'exception' => get_class($e),
                        'trace' => collect($e->getTrace())->map(fn($trace) => \Illuminate\Support\Arr::except($trace, ['args']))->take(10),
                    ];
                }

                return response()->json($data, $status);
            }

            // For non-API requests, let Laravel handle it normally
            return null;
        });
    })->create();
