<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Services\ApiKeyService;

/**
 * Validates API keys for both shop and admin REST APIs
 */
class VerifyStorefrontKey
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        $keyType = $this->getRequiredKeyType($path);

        if ($keyType === null) {
            return $next($request);
        }

        if ($this->isDocumentationEndpoint($request)) {
            return $next($request);
        }

        $key = ApiKeyService::getKeyFromRequest($request, $keyType);

        if (! $key) {
            return $this->missingKeyResponse($keyType);
        }

        if (app()->environment('testing') && $this->isTestKey($key)) {
            $request->attributes->set('api_key', [
                'id'         => 'test-key',
                'name'       => 'Test Key',
                'rate_limit' => 10000,
            ]);
            $request->attributes->set('key_type', $keyType);
            $request->attributes->set('rate_limit', [
                'allowed'   => true,
                'remaining' => 10000,
                'reset_at'  => 0,
            ]);

            return $next($request);
        }

        $ipAddress = $request->ip();

        $validation = $this->apiKeyService->validate($key, $keyType, $ipAddress);

        if (! $validation['valid']) {
            return $this->unauthorizedResponse($validation['message']);
        }

        $client = $validation['client'];
        $rateLimit = $this->apiKeyService->checkRateLimit($client);

        $request->attributes->set('api_key', $client);
        $request->attributes->set('key_type', $keyType);
        $request->attributes->set('rate_limit', $rateLimit);

        if (! $rateLimit['allowed']) {
            return $this->rateLimitExceededResponse($rateLimit);
        }

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $rateLimit);
    }

    /**
     * Determine which API key type is required for a route
     *
     * Admin routes (`/api/admin/*`) authenticate purely via the
     * `auth:admin-api` Bearer token (Webkul\BagistoApi\Admin\Auth\AdminApiGuard).
     * No X-Admin-Key header is required — returning null here skips this
     * middleware entirely for those routes.
     *
     * @return string|null ApiKeyService::KEY_TYPE_* or null
     */
    protected function getRequiredKeyType(string $path): ?string
    {
        // /api/admin/* — Bearer-token auth via AdminApiGuard, no API key needed.
        if (str_starts_with($path, '/api/admin')) {
            return null;
        }

        if (str_starts_with($path, '/api/shop')) {
            return ApiKeyService::KEY_TYPE_SHOP;
        }

        return null;
    }

    /**
     * Check if the request is to a documentation endpoint.
     * GET requests to API documentation and playgrounds bypass authentication.
     */
    protected function isDocumentationEndpoint(Request $request): bool
    {
        $path = $request->getPathInfo();
        $method = $request->method();

        if ($method !== 'GET') {
            return false;
        }

        $documentationPaths = [
            '/api',
            '/api/docs',
            '/api/shop',
            '/api/shop/docs',
            '/api/admin',
            '/api/admin/docs',
            '/api/graphql',
            '/graphql',
            '/graphiql',
            '/admin/graphiql',
            '/api/admin/graphiql',
        ];

        foreach ($documentationPaths as $docPath) {
            if ($path === $docPath || strpos($path, $docPath.'?') === 0) {
                return true;
            }
        }

        if (
            strpos($path, '/api/graphiql') === 0 ||
            strpos($path, '/api/graphql') === 0 ||
            strpos($path, '/api/graphql/playground') === 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Return missing API key response
     *
     * @param  string  $keyType  The required key type
     */
    protected function missingKeyResponse(string $keyType = 'shop'): \Illuminate\Http\JsonResponse
    {
        $headerName = $keyType === 'admin' ? 'X-Admin-Key' : 'X-STOREFRONT-KEY';

        return response()->json([
            'message'     => "{$headerName} header is required",
            'error'       => 'missing_key',
            'header_name' => $headerName,
            'key_type'    => $keyType,
        ], 401);
    }

    /**
     * Return an unauthorized JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'message' => $message,
            'error'   => 'invalid_key',
        ], 403);
    }

    /**
     * Return rate limit exceeded response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function rateLimitExceededResponse(array $rateLimit): Response
    {
        return response()->json([
            'message'     => 'Rate limit exceeded',
            'error'       => 'rate_limit_exceeded',
            'retry_after' => $rateLimit['reset_at'] ?? 60,
        ], 429);
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders(Response $response, array $rateLimit): Response
    {
        $response->headers->set('X-RateLimit-Limit', (string) config('storefront.default_rate_limit', 100));
        $response->headers->set('X-RateLimit-Remaining', (string) $rateLimit['remaining']);
        $response->headers->set('X-RateLimit-Reset', (string) ($rateLimit['reset_at'] ?? time() + 60));

        return $response;
    }

    /**
     * Check if the key is a test key (for testing environment)
     */
    protected function isTestKey(string $key): bool
    {
        return str_starts_with($key, 'pk_test_') || $key === 'test-key';
    }
}
