<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

use Webkul\BagistoApi\Exception\AuthenticationException;

class InvalidJwtException extends AuthenticationException
{
    /**
     * Create an exception for a token that is not a valid admin API token.
     */
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? trans('bagistoapi::app.admin.auth.errors.unauthenticated'));
    }

    /**
     * The machine-readable error code returned to the client.
     */
    public function errorCode(): string
    {
        return 'unauthenticated';
    }
}
