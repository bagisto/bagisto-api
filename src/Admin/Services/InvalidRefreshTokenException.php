<?php

namespace Webkul\BagistoApi\Admin\Services;

use Webkul\BagistoApi\Exception\AuthenticationException;

class InvalidRefreshTokenException extends AuthenticationException
{
    /**
     * Create an exception for a refresh token that cannot be exchanged.
     */
    public function __construct(protected bool $reused = false)
    {
        parent::__construct(trans('bagistoapi::app.admin.auth.errors.refresh-token-invalid'));
    }

    /**
     * The machine-readable error code returned to the client.
     */
    public function errorCode(): string
    {
        return 'refresh_token_invalid';
    }

    /**
     * Whether the token had already been used, which revoked its session.
     */
    public function wasReused(): bool
    {
        return $this->reused;
    }
}
