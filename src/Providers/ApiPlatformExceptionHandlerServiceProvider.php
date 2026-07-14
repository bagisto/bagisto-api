<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\Exception\UnexpectedValueException as SerializerUnexpectedValueException;

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
            ExceptionHandler::class,
            function ($wrapped) {
                return new class($wrapped) implements ExceptionHandler
                {
                    public function __construct(private $wrapped) {}

                    public function report(\Throwable $e)
                    {
                        return $this->wrapped->report($e);
                    }

                    public function render($request, \Throwable $e)
                    {
                        if ($this->isMalformedInput($e)
                            && ($request->is('api/*') || $request->expectsJson())
                        ) {
                            return response()->json([
                                'type' => '/errors/422',
                                'title' => 'Unprocessable Entity',
                                'status' => 422,
                                'detail' => $this->malformedInputDetail($e),
                            ], 422);
                        }

                        if ($e instanceof HttpExceptionInterface
                            && $e instanceof ProblemExceptionInterface
                            && ($request->is('api/*') || $request->expectsJson())
                        ) {
                            return response()->json([
                                'type' => $e->getType(),
                                'title' => $e->getTitle(),
                                'status' => $e->getStatus(),
                                'detail' => $e->getDetail(),
                            ], $e->getStatusCode());
                        }

                        return $this->wrapped->render($request, $e);
                    }

                    /**
                     * A field sent with the wrong type is rejected while the request payload is
                     * hydrated onto its input DTO — before any processor runs. That is a client
                     * error, not an internal one.
                     */
                    private function isMalformedInput(\Throwable $e): bool
                    {
                        if ($e instanceof SerializerUnexpectedValueException) {
                            return true;
                        }

                        return $e instanceof \TypeError
                            && str_contains($e->getMessage(), 'Webkul\\BagistoApi\\')
                            && str_contains($e->getMessage(), 'Cannot assign');
                    }

                    private function malformedInputDetail(\Throwable $e): string
                    {
                        if (! $e instanceof \TypeError) {
                            return $e->getMessage();
                        }

                        // "Cannot assign array to property Webkul\…\CustomerAddressInput::$address1 of type ?string"
                        if (preg_match('/Cannot assign (\S+) to property \S+::\$(\S+) of type (\S+)/', $e->getMessage(), $m)) {
                            return sprintf('The "%s" field must be of type %s, %s given.', $m[2], ltrim($m[3], '?'), $m[1]);
                        }

                        return $e->getMessage();
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
