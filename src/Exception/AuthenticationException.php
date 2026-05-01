<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

/**
 * Thrown when a request lacks credentials. Maps to HTTP 401 in REST.
 */
class AuthenticationException extends \Exception implements \GraphQL\Error\ClientAware, HttpExceptionInterface, ProblemExceptionInterface
{
    private int $status = 401;

    private array $headers = [];

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'authentication';
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
        return '/errors/401';
    }

    public function getTitle(): ?string
    {
        return 'Unauthorized';
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
