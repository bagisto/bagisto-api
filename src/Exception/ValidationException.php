<?php

namespace Webkul\BagistoApi\Exception;

use GraphQL\Error\ClientAware;

class ValidationException extends \Exception implements ClientAware
{
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
        return 'validation';
    }
}
