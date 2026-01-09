<?php

namespace Webkul\BagistoApi\Exception;

/**
 * ValidationException
 */
class ValidationException extends \Exception implements \GraphQL\Error\ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'validation';
    }
}
