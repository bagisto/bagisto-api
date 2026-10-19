<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceAdminApiAuth
{
    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_starts_with($path, '/api/admin')) {
            return $next($request);
        }

        if ($this->isDocumentationEndpoint($request)) {
            return $next($request);
        }

        $admin = Auth::guard('admin-api')->user();

        if (! $admin) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
                'error' => 'unauthenticated',
            ], 401);
        }

        return $next($request);
    }

    /**
     * Whether documentation endpoint.
     */
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
