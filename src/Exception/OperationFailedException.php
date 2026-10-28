<?php

namespace Webkul\BagistoApi\Exception;

use GraphQL\Error\ClientAware;

class OperationFailedException extends \Exception implements ClientAware
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
        return 'operation_failed';
    }
}
