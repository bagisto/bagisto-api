<?php

namespace Webkul\BagistoApi\Admin\Services\Jwt;

class ExpiredJwtException extends InvalidJwtException
{
    /**
     * Create an exception for a validly signed token past its expiry.
     */
    public function __construct(protected string $tokenType)
    {
        $isApiToken = $tokenType === JwtSigner::TYPE_API;

        parent::__construct(
            trans($isApiToken
                ? 'bagistoapi::app.admin.auth.errors.api-token-expired'
                : 'bagistoapi::app.admin.auth.errors.access-token-expired'),
            $isApiToken
                ? self::CODE_API_TOKEN_EXPIRED
                : self::CODE_ACCESS_TOKEN_EXPIRED,
        );
    }

    /**
     * The `typ` claim of the expired token.
     */
    public function tokenType(): string
    {
        return $this->tokenType;
    }
}
