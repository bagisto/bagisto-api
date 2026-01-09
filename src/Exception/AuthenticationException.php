<?php

namespace Webkul\BagistoApi\Exception;

class AuthenticationException extends \Exception implements \GraphQL\Error\ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'authentication';
    }
}
