<?php

namespace Webkul\BagistoApi\Serializer;

use GraphQL\Error\ClientAware;
use Webkul\BagistoApi\Exception\ValidationException;

class BagistoApiExceptionSerializer implements ClientAware
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function isClientSafe(): bool
    {
        return $this->exception instanceof ValidationException;
    }

    public function getCategory(): string
    {
        return 'validation';
    }

    public static function createFromException(\Throwable $exception): ClientAware
    {
        return new self($exception);
    }
}
