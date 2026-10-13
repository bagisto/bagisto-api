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

                    public function report(\Throwable $exception)
                    {
                        return $this->wrapped->report($exception);
                    }

                    public function render($request, \Throwable $exception)
                    {
                        if ($this->isMalformedInput($exception)
                            && ($request->is('api/*') || $request->expectsJson())
                        ) {
                            return response()->json([
                                'type' => '/errors/422',
                                'title' => 'Unprocessable Entity',
                                'status' => 422,
                                'detail' => $this->malformedInputDetail($exception),
                            ], 422);
                        }

                        if ($exception instanceof HttpExceptionInterface
                            && $exception instanceof ProblemExceptionInterface
                            && ($request->is('api/*') || $request->expectsJson())
                        ) {
                            return response()->json([
                                'type' => $exception->getType(),
                                'title' => $exception->getTitle(),
                                'status' => $exception->getStatus(),
                                'detail' => $exception->getDetail(),
                            ], $exception->getStatusCode());
                        }

                        return $this->wrapped->render($request, $exception);
                    }

                    /**
                     * A field sent with the wrong type is rejected while the request payload is
                     * hydrated onto its input DTO — before any processor runs. That is a client
                     * error, not an internal one.
                     */
                    private function isMalformedInput(\Throwable $exception): bool
                    {
                        if ($exception instanceof SerializerUnexpectedValueException) {
                            return true;
                        }

                        return $exception instanceof \TypeError
                            && str_contains($exception->getMessage(), 'Webkul\\BagistoApi\\')
                            && str_contains($exception->getMessage(), 'Cannot assign');
                    }

                    private function malformedInputDetail(\Throwable $exception): string
                    {
                        if (! $exception instanceof \TypeError) {
                            return $exception->getMessage();
                        }

                        // "Cannot assign array to property Webkul\…\CustomerAddressInput::$address1 of type ?string"
                        if (preg_match('/Cannot assign (\S+) to property \S+::\$(\S+) of type (\S+)/', $exception->getMessage(), $matches)) {
                            return sprintf('The "%s" field must be of type %s, %s given.', $matches[2], ltrim($matches[3], '?'), $matches[1]);
                        }

                        return $exception->getMessage();
                    }

                    public function renderForConsole($output, \Throwable $exception)
                    {
                        return $this->wrapped->renderForConsole($output, $exception);
                    }

                    public function shouldReport(\Throwable $exception)
                    {
                        return $this->wrapped->shouldReport($exception);
                    }
                };
            }
        );
    }
}
