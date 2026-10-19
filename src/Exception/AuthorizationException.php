<?php

namespace Webkul\BagistoApi\Exception;

use ApiPlatform\Metadata\Exception\HttpExceptionInterface;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use GraphQL\Error\ClientAware;

class AuthorizationException extends \Exception implements ClientAware, HttpExceptionInterface, ProblemExceptionInterface
{
    /**
     * The status.
     */
    private int $status = 403;

    /**
     * The headers.
     */
    private array $headers = [];

    /**
     * Whether client safe.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Get category.
     */
    public function getCategory(): string
    {
        return 'authorization';
    }

    /**
     * Get status code.
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Get headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return '/errors/403';
    }

    /**
     * Get title.
     */
    public function getTitle(): ?string
    {
        return 'Forbidden';
    }

    /**
     * Get status.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Get detail.
     */
    public function getDetail(): ?string
    {
        return $this->message;
    }

    /**
     * Get instance.
     */
    public function getInstance(): ?string
    {
        return null;
    }
}
