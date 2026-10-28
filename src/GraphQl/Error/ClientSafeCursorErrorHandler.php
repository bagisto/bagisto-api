<?php

namespace Webkul\BagistoApi\GraphQl\Error;

use ApiPlatform\GraphQl\Error\ErrorHandlerInterface;
use ApiPlatform\GraphQl\State\Processor\NormalizeProcessor;
use GraphQL\Error\Error;
use Webkul\BagistoApi\Exception\InvalidInputException;

class ClientSafeCursorErrorHandler implements ErrorHandlerInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(private readonly ErrorHandlerInterface $decorated) {}

    /**
     * Report an invalid pagination cursor to the client before handing the errors on.
     */
    public function __invoke(array $errors, callable $formatter): array
    {
        return ($this->decorated)(array_map(fn ($error) => $this->clientSafe($error), $errors), $formatter);
    }

    /**
     * Rebuild an invalid cursor error as a client-safe invalid input error with the same message.
     */
    protected function clientSafe(mixed $error): mixed
    {
        if (
            ! $error instanceof Error
            || ! $this->isInvalidCursor($error->getPrevious())
        ) {
            return $error;
        }

        return new Error(
            $error->getMessage(),
            $error->getNodes(),
            $error->getSource(),
            $error->getPositions(),
            $error->getPath(),
            new InvalidInputException($error->getMessage(), 400, $error->getPrevious()),
            $error->getExtensions(),
            $error->getUnaliasedPath(),
        );
    }

    /**
     * Determine whether API Platform refused a cursor argument it could not decode.
     */
    protected function isInvalidCursor(?\Throwable $exception): bool
    {
        return $exception instanceof \UnexpectedValueException
            && $exception->getFile() === (new \ReflectionClass(NormalizeProcessor::class))->getFileName()
            && preg_match('/^(Empty cursor is invalid|Cursor .+ is invalid)$/s', $exception->getMessage()) === 1;
    }
}
