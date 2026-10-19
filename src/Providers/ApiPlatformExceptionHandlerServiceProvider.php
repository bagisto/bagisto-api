<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\Exception\UnexpectedValueException as SerializerUnexpectedValueException;

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
                    public function __construct(private $wrapped) {}

                    public function report(\Throwable $exception)
                    {
                        return $this->wrapped->report($exception);
                    }

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
                            return response()->json([
                                'type' => $exception->getType(),
                                'title' => $exception->getTitle(),
                                'status' => $exception->getStatus(),
                                'detail' => $exception->getDetail(),
                            ], $exception->getStatusCode());
                        }

                        return $this->wrapped->render($request, $exception);
                    }

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
