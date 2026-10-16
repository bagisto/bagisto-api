<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Applies a per-IP limit to a public endpoint, the way core's throttle middleware does on the web routes.
 */
trait ThrottlesGuestRequests
{
    protected function throttleGuestRequest(string $name, int $maxAttempts): void
    {
        $key = $name.':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.eu-withdrawal.too-many-attempts'), 429);
        }

        RateLimiter::hit($key, 60);
    }
}
