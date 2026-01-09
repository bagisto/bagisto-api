<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

/**
 * InvalidInputException
 *
 * Thrown when user input validation fails.
 * This covers required field validation, data type validation, and business logic validation.
 *
 * Examples:
 * - Missing required fields (productId, quantity, cartItemId)
 * - Invalid quantity value
 * - Missing coupon code
 * - Invalid address data
 *
 * Status Code: 400 Bad Request
 */
class InvalidInputException extends \Exception implements \GraphQL\Error\ClientAware, HttpExceptionInterface, ProblemExceptionInterface
{
    private int $status = 400;

    private array $headers = [];

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'invalid_input';
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return '/errors/400';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): ?string
    {
        return 'Bad Request';
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail(): ?string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(): ?string
    {
        return null;
    }
}
