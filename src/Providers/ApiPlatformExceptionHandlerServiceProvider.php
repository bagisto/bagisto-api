<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as SymfonyHttpExceptionInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException as SerializerUnexpectedValueException;
use Webkul\BagistoApi\Exception\AdminAuthException;

class ApiPlatformExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Boot the package services.
     */
    public function boot(): void
    {
        $this->app->extend(
            ExceptionHandler::class,
            function ($wrapped) {
                return new class($wrapped) implements ExceptionHandler
                {
                    /**
                     * Create a new instance around the wrapped handler.
                     */
                    public function __construct(private $wrapped) {}

                    /**
                     * Report the exception through the wrapped handler.
                     */
                    public function report(\Throwable $exception)
                    {
                        return $this->wrapped->report($exception);
                    }

                    /**
                     * Render the exception, answering API requests with JSON and their real status.
                     */
                    public function render($request, \Throwable $exception)
                    {
                        if (
                            $this->isMalformedInput($exception)
                            && (
                                $request->is('api/*')
                                || $request->expectsJson()
                            )
                        ) {
                            return response()->json([
                                'type' => '/errors/422',
                                'title' => 'Unprocessable Entity',
                                'status' => 422,
                                'detail' => $this->malformedInputDetail($exception),
                            ], 422);
                        }

                        if (
                            $exception instanceof HttpExceptionInterface
                            && $exception instanceof ProblemExceptionInterface
                            && (
                                $request->is('api/*')
                                || $request->expectsJson()
                            )
                        ) {
                            $body = [
                                'type' => $exception->getType(),
                                'title' => $exception->getTitle(),
                                'status' => $exception->getStatus(),
                                'detail' => $exception->getDetail(),
                            ];

                            if ($exception instanceof AdminAuthException) {
                                $body += $exception->toResponseBody();
                            }

                            return response()->json($body, $exception->getStatusCode(), $exception->getHeaders());
                        }

                        if ($this->isUnroutedApiRequest($request, $exception)) {
                            return $this->renderRoutingException($exception);
                        }

                        return $this->wrapped->render($request, $exception);
                    }

                    /**
                     * Render the exception for the console through the wrapped handler.
                     */
                    public function renderForConsole($output, \Throwable $exception)
                    {
                        return $this->wrapped->renderForConsole($output, $exception);
                    }

                    /**
                     * Determine through the wrapped handler whether the exception is reported.
                     */
                    public function shouldReport(\Throwable $exception)
                    {
                        return $this->wrapped->shouldReport($exception);
                    }

                    /**
                     * Determine whether the exception comes from malformed request input.
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

                    /**
                     * Build the client-facing detail for a malformed input exception.
                     */
                    private function malformedInputDetail(\Throwable $exception): string
                    {
                        if (! $exception instanceof \TypeError) {
                            return $exception->getMessage();
                        }

                        if (preg_match('/Cannot assign (\S+) to property \S+::\$(\S+) of type (\S+)/', $exception->getMessage(), $matches)) {
                            return sprintf('The "%s" field must be of type %s, %s given.', $matches[2], ltrim($matches[3], '?'), $matches[1]);
                        }

                        return $exception->getMessage();
                    }

                    /**
                     * Determine whether the router refused an API request before any route matched.
                     */
                    private function isUnroutedApiRequest($request, \Throwable $exception): bool
                    {
                        return $exception instanceof SymfonyHttpExceptionInterface
                            && $request->is('api/*')
                            && $request->route() === null;
                    }

                    /**
                     * Render a routing exception as JSON with its own status and headers.
                     */
                    private function renderRoutingException(SymfonyHttpExceptionInterface $exception): JsonResponse
                    {
                        $status = $exception->getStatusCode();

                        return response()->json([
                            'type' => '/errors/'.$status,
                            'title' => Response::$statusTexts[$status] ?? 'Error',
                            'status' => $status,
                            'detail' => $exception->getMessage(),
                            'message' => $exception->getMessage(),
                        ], $status, $exception->getHeaders());
                    }
                };
            }
        );
    }
}
