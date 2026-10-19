<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class RateLimitApi
{
    /**
     * Cache key prefix for rate limiting.
     */
    private const CACHE_PREFIX = 'api:rate-limit:';

    /**
     * Create a new instance.
     */
    public function __construct(protected RateLimiter $limiter) {}

    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkipRateLimit($request)) {
            return $next($request);
        }

        $limit = $this->getRateLimit($request);

        if (! $limit) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request);

        if (! $this->allowRequest($key, $limit)) {
            return $this->buildResponse($request, $limit, $key);
        }

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key, $limit);
    }

    /**
     * Should skip rate limit.
     */
    private function shouldSkipRateLimit(Request $request): bool
    {
        if (in_array($request->path(), ['health', 'ping', ''])) {
            return true;
        }

        if (
            $request->ip() === '127.0.0.1'
            || $request->ip() === '::1'
        ) {
            return config('api-platform.rate_limit.skip_localhost', true);
        }

        return false;
    }

    /**
     * Get rate limit.
     */
    private function getRateLimit(Request $request): ?array
    {
        $path = $request->path();

        if (str_starts_with($path, 'api/auth/')) {
            return [
                'max_attempts' => config('api-platform.rate_limit.auth', 5),
                'decay_minutes' => 1,
                'message' => 'Too many authentication attempts. Please try again later.',
            ];
        }

        if (str_starts_with($path, 'api/admin/')) {
            return [
                'max_attempts' => config('api-platform.rate_limit.admin', 60),
                'decay_minutes' => 1,
                'message' => 'Rate limit exceeded for admin API.',
            ];
        }

        if (str_starts_with($path, 'api/shop/')) {
            return [
                'max_attempts' => config('api-platform.rate_limit.shop', 100),
                'decay_minutes' => 1,
                'message' => 'Rate limit exceeded. Please try again later.',
            ];
        }

        if (str_starts_with($path, 'api/graphql')) {
            return [
                'max_attempts' => config('api-platform.rate_limit.graphql', 100),
                'decay_minutes' => 1,
                'message' => 'Rate limit exceeded for GraphQL queries.',
            ];
        }

        return null;
    }

    /**
     * Resolve request signature for rate limiting
     * Uses API key if available (better for tracking), otherwise IP address.
     */
    private function resolveRequestSignature(Request $request): string
    {
        if ($apiKey = $request->header('X-STOREFRONT-KEY')) {
            return self::CACHE_PREFIX.hash('sha256', $apiKey);
        }

        return self::CACHE_PREFIX.'ip:'.hash('sha256', $request->ip() ?? '');
    }

    /**
     * Check if request is allowed based on rate limit.
     */
    private function allowRequest(string $key, array $limit): bool
    {
        $attempts = cache()->get($key, 0);

        if ($attempts >= $limit['max_attempts']) {
            return false;
        }

        if ($attempts === 0) {
            cache()->put($key, 1, now()->addMinutes($limit['decay_minutes']));
        } else {
            cache()->increment($key);
        }

        return true;
    }

    /**
     * Build rate limit exceeded response.
     */
    private function buildResponse(Request $request, array $limit, string $key)
    {
        $retryAfter = $this->getRetryAfter($key, $limit);

        return response()->json([
            'error' => 'rate_limit_exceeded',
            'message' => $limit['message'],
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => (string) $retryAfter,
        ]);
    }

    /**
     * Get retry-after seconds from cache TTL.
     */
    private function getRetryAfter(string $key, array $limit): int
    {
        $ttl = cache()->getStore()->connection()->ttl($key);

        if ($ttl > 0) {
            return $ttl;
        }

        if ($ttl > time()) {
            return max(1, $ttl - time());
        }

        return max(1, $limit['decay_minutes'] * 60);
    }

    /**
     * Add rate limit headers to response.
     */
    private function addRateLimitHeaders($response, string $key, array $limit)
    {
        $attempts = cache()->get($key, 0);
        $remaining = max(0, $limit['max_attempts'] - $attempts);

        $response->headers->set('X-RateLimit-Limit', (string) $limit['max_attempts']);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) now()->addMinutes($limit['decay_minutes'])->timestamp);

        return $response;
    }
}
