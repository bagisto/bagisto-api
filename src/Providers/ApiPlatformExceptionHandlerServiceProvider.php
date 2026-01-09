<?php

namespace Webkul\BagistoApi\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Webkul\BagistoApi\Exception\InvalidInputException;

class ApiPlatformExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend the exception handler to handle InvalidInputException specifically for API requests
        $this->app->extend(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            function ($wrapped) {
                // Create a wrapper that intercepts the render method
                return new class($wrapped) implements \Illuminate\Contracts\Debug\ExceptionHandler
                {
                    public function __construct(private $wrapped) {}

                    public function report(\Throwable $e)
                    {
                        return $this->wrapped->report($e);
                    }

                    public function render($request, \Throwable $e)
                    {
                        Log::info('Exception handler render called', [
                            'exception'        => get_class($e),
                            'is_invalid_input' => $e instanceof InvalidInputException,
                            'is_api_request'   => $request->is('api/*'),
                            'expects_json'     => $request->expectsJson(),
                        ]);

                        // Handle InvalidInputException for API requests
                        if ($e instanceof InvalidInputException && ($request->is('api/*') || $request->expectsJson())) {
                            Log::info('Rendering InvalidInputException as JSON', ['message' => $e->getMessage()]);

                            return response()->json([
                                'type'   => $e->getType(),
                                'title'  => $e->getTitle(),
                                'status' => $e->getStatus(),
                                'detail' => $e->getDetail(),
                            ], $e->getStatusCode());
                        }

                        return $this->wrapped->render($request, $e);
                    }

                    public function renderForConsole($output, \Throwable $e)
                    {
                        return $this->wrapped->renderForConsole($output, $e);
                    }

                    public function shouldReport(\Throwable $e)
                    {
                        return $this->wrapped->shouldReport($e);
                    }
                };
            }
        );
    }
}
