<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Render BagistoApi exceptions implementing HttpExceptionInterface +
 * ProblemExceptionInterface as RFC 7807 JSON with their declared status code,
 * for any API request. Without this wrapper Laravel's default exception
 * handler maps everything except a small set of built-ins to HTTP 500.
 */
class ApiPlatformExceptionHandlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->extend(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            function ($wrapped) {
                return new class($wrapped) implements \Illuminate\Contracts\Debug\ExceptionHandler
                {
                    public function __construct(private $wrapped) {}

                    public function report(\Throwable $e)
                    {
                        return $this->wrapped->report($e);
                    }

                    public function render($request, \Throwable $e)
                    {
                        if ($e instanceof HttpExceptionInterface
                            && $e instanceof ProblemExceptionInterface
                            && ($request->is('api/*') || $request->expectsJson())
                        ) {
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
