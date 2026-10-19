<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAdminApi extends ThrottleRequests
{
    /**
     * Handle the request.
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        if (! $this->isAdminApiRequest($request)) {
            return $next($request);
        }

        try {
            return parent::handle($request, $next, 'admin-api');
        } catch (ThrottleRequestsException $exception) {
            return new JsonResponse([
                'message' => 'Too Many Attempts.',
                'error' => 'rate_limit_exceeded',
            ], 429, $exception->getHeaders());
        }
    }

    /**
     * Whether admin api request.
     */
    protected function isAdminApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/admin');
    }
}
