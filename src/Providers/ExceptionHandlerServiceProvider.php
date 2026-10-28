<?php

namespace Webkul\BagistoApi\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Webkul\BagistoApi\Exception\InvalidInputException;

class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $exceptionHandler = app()->make(ExceptionHandler::class);

        if (method_exists($exceptionHandler, 'renderable')) {
            $exceptionHandler->renderable(function (InvalidInputException $exception, $request) {
                if (
                    $request->is('api/*')
                    || $request->expectsJson()
                ) {
                    return response()->json([
                        'type' => $exception->getType(),
                        'title' => $exception->getTitle(),
                        'status' => $exception->getStatus(),
                        'detail' => $exception->getDetail(),
                    ], $exception->getStatusCode());
                }
            });
        }
    }
}
