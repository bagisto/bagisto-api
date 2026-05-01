<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

/**
 * AuthorizationException
 *
 * Thrown when a request is unauthenticated or the authenticated user lacks permission
 * for the resource. Maps to HTTP 403 in REST and an `errors[]` entry in GraphQL.
 */
class AuthorizationException extends \Exception implements \GraphQL\Error\ClientAware, HttpExceptionInterface, ProblemExceptionInterface
{
    private int $status = 403;

    private array $headers = [];

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'authorization';
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
        return '/errors/403';
    }

    public function getTitle(): ?string
    {
        return 'Forbidden';
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
