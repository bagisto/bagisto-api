<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Auth\AdminApiGuard;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\BagistoApi\Http\Middleware\Concerns\InspectsGraphQlRootFields;

class EnforceAdminApiAuth
{
    use InspectsGraphQlRootFields;

    /**
     * The admin REST paths reachable without a token, all of them POST.
     */
    public const PUBLIC_REST_PATHS = [
        '/api/admin/auth/login',
        '/api/admin/auth/two-factor',
        '/api/admin/auth/refresh',
        '/api/admin/auth/forgot-password',
        '/api/admin/auth/reset-password',
    ];

    /**
     * The admin GraphQL mutations reachable without a token.
     */
    public const PUBLIC_GRAPHQL_MUTATIONS = [
        'createAdminLogin',
        'createAdminTwoFactorVerify',
        'createAdminTokenRefresh',
        'createAdminForgotPassword',
        'createAdminResetPassword',
    ];

    /**
     * The admin GraphQL endpoint.
     */
    public const GRAPHQL_PATH = '/api/admin/graphql';

    /**
     * Reject an admin API request without a valid access token or API token, except the public auth operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_starts_with($path, '/api/admin')) {
            return $next($request);
        }

        if (
            $this->isDocumentationEndpoint($request)
            || $this->isPublicOperation($request)
        ) {
            return $next($request);
        }

        $guard = Auth::guard('admin-api');

        if ($guard->user()) {
            return $next($request);
        }

        $failure = $guard instanceof AdminApiGuard
            ? $guard->failure()
            : null;

        return $this->reject($request, $failure ?? new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.unauthenticated'),
        ));
    }

    /**
     * Whether the request is one of the login, two-factor, refresh or password operations.
     */
    protected function isPublicOperation(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $path = rtrim($request->getPathInfo(), '/');

        if ($path === self::GRAPHQL_PATH) {
            return $this->documentSelectsOnlyMutations($this->graphQlDocument($request), self::PUBLIC_GRAPHQL_MUTATIONS);
        }

        return in_array($path, self::PUBLIC_REST_PATHS, true);
    }

    /**
     * The 401 or 403 response for a rejected request, shaped for REST or for GraphQL.
     */
    protected function reject(Request $request, AdminAuthException $exception): JsonResponse
    {
        $body = $exception->toResponseBody();

        if (rtrim($request->getPathInfo(), '/') === self::GRAPHQL_PATH) {
            $body['errors'] = [[
                'message' => $exception->getMessage(),
                'extensions' => $exception->getExtensions(),
            ]];
        }

        return new JsonResponse($body, $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * Whether the request reads the admin API documentation.
     */
    protected function isDocumentationEndpoint(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        return in_array($request->getPathInfo(), [
            '/api/admin',
            '/api/admin/docs',
            '/api/admin/graphiql',
        ], true);
    }
}
