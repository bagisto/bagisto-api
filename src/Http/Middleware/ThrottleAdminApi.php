<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;

class ThrottleAdminApi extends ThrottleRequests
{
    /**
     * The name of the rate limiter the admin API is throttled by.
     */
    public const LIMITER = 'admin-api';

    /**
     * Throttle an admin API request by the limits of its session's admin or its API token.
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): Response
    {
        if (! $this->isAdminApiRequest($request)) {
            return $next($request);
        }

        try {
            return parent::handle($request, $next, self::LIMITER);
        } catch (ThrottleRequestsException $exception) {
            return new JsonResponse([
                'message' => trans('bagistoapi::app.admin.auth.errors.rate-limit-exceeded'),
                'error' => 'rate_limit_exceeded',
            ], 429, $exception->getHeaders());
        }
    }

    /**
     * The limit of a request: per admin for a session, per API token for an API token, per address otherwise.
     */
    public static function limitFor(Request $request, AdminApiAuthSettings $settings): Limit
    {
        $credential = AdminAuthHelper::credential();

        if ($credential === null) {
            return app()->environment('testing')
                ? Limit::none()
                : Limit::perMinute($settings->unauthenticatedRequestsPerMinute())->by('admin-api-unauthenticated:'.$request->ip());
        }

        if ($credential->isApiToken()) {
            return Limit::perMinute($settings->apiTokenRequestsPerMinute())
                ->by('admin-api-token:'.$credential->id());
        }

        return Limit::perMinute($settings->sessionRequestsPerMinute())
            ->by('admin-api-admin:'.$credential->admin->id);
    }

    /**
     * Whether the request is for the admin API.
     */
    protected function isAdminApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/admin');
    }
}
