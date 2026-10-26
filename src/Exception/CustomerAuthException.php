<?php

namespace Webkul\BagistoApi\Exception;

class CustomerAuthException extends ApiAuthException
{
    /**
     * The machine-readable code of a login by a customer who has not verified their email address.
     */
    public const CODE_ACCOUNT_UNVERIFIED = 'account_unverified';

    /**
     * The machine-readable code of too many token refreshes in one session.
     */
    public const CODE_TOO_MANY_REFRESH_REQUESTS = 'too_many_refresh_requests';

    /**
     * The machine-readable code of a signed-in customer over the per-minute request limit.
     */
    public const CODE_RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
}
