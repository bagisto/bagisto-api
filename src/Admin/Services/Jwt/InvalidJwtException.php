<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

use Webkul\BagistoApi\Exception\AdminAuthException;

class InvalidJwtException extends AdminAuthException
{
    /**
     * Create an exception for a token that is not a valid admin API token.
     */
    public function __construct(?string $message = null, string $errorCode = self::CODE_UNAUTHENTICATED)
    {
        parent::__construct(
            $message ?? trans('bagistoapi::app.admin.auth.errors.unauthenticated'),
            $errorCode,
        );
    }
}
