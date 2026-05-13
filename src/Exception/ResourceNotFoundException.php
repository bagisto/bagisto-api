<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

/**
 * Thrown when a requested resource (cart, product, item, etc.) is not found.
 * Maps to HTTP 404 in REST and an `errors[]` entry in GraphQL.
 */
class ResourceNotFoundException extends \Exception implements \GraphQL\Error\ClientAware, HttpExceptionInterface, ProblemExceptionInterface
{
    private int $status = 404;

    private array $headers = [];

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'resource_not_found';
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getType(): string
    {
        return '/errors/404';
    }

    public function getTitle(): ?string
    {
        return 'Not Found';
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getDetail(): ?string
    {
        return $this->message;
    }

    public function getInstance(): ?string
    {
        return null;
    }
}
