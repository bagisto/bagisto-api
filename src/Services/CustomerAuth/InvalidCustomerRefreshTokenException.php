<?php

namespace Webkul\BagistoApi\Services\CustomerAuth;

use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Models\CustomerApiSession;

class InvalidCustomerRefreshTokenException extends CustomerAuthException
{
    /**
     * Create an exception for a customer refresh token that cannot be exchanged.
     */
    public function __construct(
        protected bool $reused = false,
        protected ?CustomerApiSession $session = null,
    ) {
        parent::__construct(
            trans('bagistoapi::app.graphql.auth.refresh-token-invalid'),
            self::CODE_REFRESH_TOKEN_INVALID,
        );
    }

    /**
     * Whether the token had already been used, which revoked its session.
     */
    public function wasReused(): bool
    {
        return $this->reused;
    }

    /**
     * The session the token belonged to, when it was found.
     */
    public function session(): ?CustomerApiSession
    {
        return $this->session;
    }
}
