<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Path-scoped admin authentication gate.
 *
 * API Platform applies its middleware list to every API route globally — we
 * can't selectively bind `auth:admin-api` only to `/api/admin/*` via the
 * config. This middleware sits in the global pipeline and short-circuits to
 * 401 when the request hits `/api/admin/*` without a valid admin Bearer
 * token resolvable by `Webkul\BagistoApi\Admin\Auth\AdminApiGuard`.
 *
 * Other routes (`/api/shop/*`, `/api/graphql`, documentation pages, etc.)
 * pass through untouched.
 *
 * Throttling is applied separately via the `throttle:admin-api` limiter
 * registered in BagistoApiServiceProvider::bootAdminIntegration().
 */
class EnforceAdminApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_starts_with($path, '/api/admin')) {
            return $next($request);
        }

        // Allow documentation / OpenAPI spec endpoints without a Bearer.
        // Mirrors the GET-bypass already in VerifyStorefrontKey for /api/admin
        // and /api/admin/docs.
        if ($this->isDocumentationEndpoint($request)) {
            return $next($request);
        }

        $admin = Auth::guard('admin-api')->user();

        if (! $admin) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
                'error'   => 'unauthenticated',
            ], 401);
        }

        return $next($request);
    }

    protected function isDocumentationEndpoint(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        $path = $request->getPathInfo();

        return in_array($path, [
            '/api/admin',
            '/api/admin/docs',
            '/api/admin/graphiql',
        ], true);
    }
}
