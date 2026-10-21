<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

class ExpiredJwtException extends InvalidJwtException
{
    /**
     * Create an exception for a validly signed token past its expiry.
     */
    public function __construct(protected string $tokenType)
    {
        parent::__construct(trans($tokenType === JwtSigner::TYPE_API
            ? 'bagistoapi::app.admin.auth.errors.api-token-expired'
            : 'bagistoapi::app.admin.auth.errors.access-token-expired'));
    }

    /**
     * The machine-readable error code returned to the client.
     */
    public function errorCode(): string
    {
        return $this->tokenType === JwtSigner::TYPE_API
            ? 'api_token_expired'
            : 'access_token_expired';
    }

    /**
     * The `typ` claim of the expired token.
     */
    public function tokenType(): string
    {
        return $this->tokenType;
    }
}
