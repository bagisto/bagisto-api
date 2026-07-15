<?php

namespace Webkul\BagistoApi\Exception;

use GraphQL\Error\ClientAware;

/**
 * ValidationException
 */
class ValidationException extends \Exception implements ClientAware
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
