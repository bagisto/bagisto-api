<?php

namespace Webkul\BagistoApi\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\BagistoApi\Exception\InvalidInputException;

class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Get the exception handler that was already initialized
        $exceptionHandler = app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        // Register exception renderer for InvalidInputException
        if (method_exists($exceptionHandler, 'renderable')) {
            $exceptionHandler->renderable(function (InvalidInputException $exception, $request) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'type'   => $exception->getType(),
                        'title'  => $exception->getTitle(),
                        'status' => $exception->getStatus(),
                        'detail' => $exception->getDetail(),
                    ], $exception->getStatusCode());
                }
            });
        }
    }
}
