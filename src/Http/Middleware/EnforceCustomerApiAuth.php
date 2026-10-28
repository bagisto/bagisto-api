<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Auth\CustomerApiGuard;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Http\Middleware\Concerns\InspectsGraphQlRootFields;
use Webkul\BagistoApi\Support\CurrentCustomer;

class EnforceCustomerApiAuth
{
    use InspectsGraphQlRootFields;

    /**
     * The storefront REST paths that sign a customer in, all of them POST, which a stale bearer never blocks.
     */
    public const PUBLIC_REST_PATHS = [
        '/api/shop/customer/login',
        '/api/shop/customer/refresh',
        '/api/shop/customers',
        '/api/shop/customers/social-login',
        '/api/shop/forgot-passwords',
        '/api/shop/reset-passwords',
    ];

    /**
     * The storefront GraphQL mutations that sign a customer in, which a stale bearer never blocks.
     */
    public const PUBLIC_GRAPHQL_MUTATIONS = [
        'createCustomerLogin',
        'createCustomerTokenRefresh',
        'createCustomer',
        'createSocialLogin',
        'createForgotPassword',
        'createResetPassword',
    ];

    /**
     * The storefront GraphQL endpoint.
     */
    public const GRAPHQL_PATH = '/api/graphql';

    /**
     * Answer a storefront request whose customer access token is expired, revoked or forged with its code.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $this->carriesCustomerToken($request)
            || $this->isPublicOperation($request)
        ) {
            return $next($request);
        }

        if (CurrentCustomer::user()) {
            return $next($request);
        }

        return $this->reject($request, CurrentCustomer::failure() ?? new CustomerAuthException(
            trans('bagistoapi::app.graphql.auth.invalid-or-expired-token'),
        ));
    }

    /**
     * Whether the request is a storefront API request whose bearer says it is a customer access token.
     */
    protected function carriesCustomerToken(Request $request): bool
    {
        $path = rtrim($request->getPathInfo(), '/');

        $bearer = $request->bearerToken();

        return (
            str_starts_with($path, '/api/shop/')
            || $path === self::GRAPHQL_PATH
        )
            && $bearer !== null
            && CustomerApiGuard::claimsCustomerAccess($bearer);
    }

    /**
     * Whether the request is one of the login, registration, refresh or password operations.
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
     * The 401 response for a rejected request, shaped for REST or for GraphQL.
     */
    protected function reject(Request $request, CustomerAuthException $exception): JsonResponse
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
}
