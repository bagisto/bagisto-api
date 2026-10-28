<?php

namespace Webkul\BagistoApi\Serializer;

use GraphQL\Error\ClientAware;
use Webkul\BagistoApi\Exception\ValidationException;

class BagistoApiExceptionSerializer implements ClientAware
{
    /**
     * The exception.
     */
    private $exception;

    /**
     * Create a new instance.
     */
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Whether client safe.
     */
    public function isClientSafe(): bool
    {
        return $this->exception instanceof ValidationException;
    }

    /**
     * Get category.
     */
    public function getCategory(): string
    {
        return 'validation';
    }

    /**
     * Create from exception.
     */
    public static function createFromException(\Throwable $exception): ClientAware
    {
        return new self($exception);
    }
}
