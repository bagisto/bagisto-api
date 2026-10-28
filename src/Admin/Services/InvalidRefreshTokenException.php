<?php

namespace Webkul\BagistoApi\Admin\Services;

use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Exception\AdminAuthException;

class InvalidRefreshTokenException extends AdminAuthException
{
    /**
     * Create an exception for a refresh token that cannot be exchanged.
     */
    public function __construct(
        protected bool $reused = false,
        protected ?AdminApiSession $session = null,
    ) {
        parent::__construct(
            trans('bagistoapi::app.admin.auth.errors.refresh-token-invalid'),
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
    public function session(): ?AdminApiSession
    {
        return $this->session;
    }
}
