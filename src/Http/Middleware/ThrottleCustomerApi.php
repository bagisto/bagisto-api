<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Auth\CustomerApiGuard;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthSettings;
use Webkul\BagistoApi\Support\CurrentCustomer;

class ThrottleCustomerApi
{
    /**
     * Create a new customer throttle.
     */
    public function __construct(
        protected RateLimiter $limiter,
        protected CustomerApiAuthSettings $settings,
    ) {}

    /**
     * Limit the storefront requests of each signed-in customer, on top of the storefront key's own limit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isCustomerRequest($request)) {
            return $next($request);
        }

        $customer = CurrentCustomer::user();

        if (! $customer) {
            return $next($request);
        }

        $key = 'bagistoapi-customer-requests:'.$customer->id;

        $limit = $this->settings->requestsPerMinute();

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            $seconds = max(1, $this->limiter->availableIn($key));

            return new JsonResponse([
                'message' => trans('bagistoapi::app.graphql.auth.rate-limit-exceeded'),
                'error' => CustomerAuthException::CODE_RATE_LIMIT_EXCEEDED,
            ], 429, [
                'Retry-After' => (string) $seconds,
                'X-RateLimit-Limit' => (string) $limit,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        $this->limiter->hit($key, 60);

        return $next($request);
    }

    /**
     * Whether the request is a storefront API request carrying a customer access token.
     */
    protected function isCustomerRequest(Request $request): bool
    {
        $path = ltrim($request->getPathInfo(), '/');

        $bearer = $request->bearerToken();

        return (
            str_starts_with($path, 'api/shop/')
            || $path === 'api/graphql'
        )
            && $bearer !== null
            && CustomerApiGuard::looksLikeAccessToken($bearer);
    }
}
