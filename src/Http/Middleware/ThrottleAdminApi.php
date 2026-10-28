<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Path-scoped admin API throttling.
 *
 * API Platform registers every `/api/admin/*` route itself and applies one
 * global middleware list, so `throttle:admin-api` cannot be bound to an admin
 * route group. This middleware sits in that global list and delegates to the
 * `admin-api` named limiter only for `/api/admin/*`, mirroring how
 * {@see EnforceAdminApiAuth} scopes authentication.
 *
 * The limiter reads `rate_limit_per_minute` / `rate_limit_per_day` off the
 * resolved token, so a token with NULL on both columns is unthrottled.
 *
 * The 429 is rendered here rather than thrown: the application's exception
 * handler turns a bare ThrottleRequestsException into an HTML 500 for these
 * routes, so the middleware emits the JSON body itself — the same approach
 * EnforceAdminApiAuth takes for its 401.
 */
class ThrottleAdminApi extends ThrottleRequests
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        if (! $this->isAdminApiRequest($request)) {
            return $next($request);
        }

        try {
            // Three args exactly — ThrottleRequests only resolves a named limiter
            // when func_num_args() === 3.
            return parent::handle($request, $next, 'admin-api');
        } catch (ThrottleRequestsException $e) {
            return new JsonResponse([
                'message' => 'Too Many Attempts.',
                'error' => 'rate_limit_exceeded',
            ], 429, $e->getHeaders());
        }
    }

    protected function isAdminApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/admin');
    }
}
