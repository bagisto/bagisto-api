<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

/**
 * Implements rate limiting for API endpoints to prevent abuse
 */
class RateLimitApi
{
    /**
     * Cache key prefix for rate limiting
     */
    private const CACHE_PREFIX = 'api:rate-limit:';

    public function __construct(protected RateLimiter $limiter) {}

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

    private function shouldSkipRateLimit(Request $request): bool
    {
        if (in_array($request->path(), ['health', 'ping', ''])) {
            return true;
        }

        if ($request->ip() === '127.0.0.1' || $request->ip() === '::1') {
            return config('api-platform.rate_limit.skip_localhost', true);
        }

        return false;
    }

    private function getRateLimit(Request $request): ?array
    {
        $path = $request->path();

        if (str_starts_with($path, 'api/auth/')) {
            return [
                'max_attempts'  => config('api-platform.rate_limit.auth', 5),
                'decay_minutes' => 1,
                'message'       => 'Too many authentication attempts. Please try again later.',
            ];
        }

        if (str_starts_with($path, 'api/admin/')) {
            return [
                'max_attempts'  => config('api-platform.rate_limit.admin', 60),
                'decay_minutes' => 1,
                'message'       => 'Rate limit exceeded for admin API.',
            ];
        }

        if (str_starts_with($path, 'api/shop/')) {
            return [
                'max_attempts'  => config('api-platform.rate_limit.shop', 100),
                'decay_minutes' => 1,
                'message'       => 'Rate limit exceeded. Please try again later.',
            ];
        }

        if (str_starts_with($path, 'api/graphql')) {
            return [
                'max_attempts'  => config('api-platform.rate_limit.graphql', 100),
                'decay_minutes' => 1,
                'message'       => 'Rate limit exceeded for GraphQL queries.',
            ];
        }

        return null;
    }

    /**
     * Resolve request signature for rate limiting
     * Uses API key if available (better for tracking), otherwise IP address
     *
     * Prefers API key as it's:
     * - More accurate (per-key limits vs per-IP)
     * - Supports shared IPs (offices, proxies)
     * - Can be different for different key tiers
     */
    private function resolveRequestSignature(Request $request): string
    {
        if ($apiKey = $request->header('X-STOREFRONT-KEY')) {
            return self::CACHE_PREFIX.hash('sha256', $apiKey);
        }

        return self::CACHE_PREFIX.'ip:'.hash('sha256', $request->ip() ?? '');
    }

    /**
     * Check if request is allowed based on rate limit
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
     * Build rate limit exceeded response
     */
    private function buildResponse(Request $request, array $limit, string $key)
    {
        $retryAfter = $this->getRetryAfter($key, $limit);

        return response()->json([
            'error'       => 'rate_limit_exceeded',
            'message'     => $limit['message'],
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => (string) $retryAfter,
        ]);
    }

    /**
     * Get retry-after seconds from cache TTL
     */
    private function getRetryAfter(string $key, array $limit): int
    {
        // Try to get TTL from cache backend
        $ttl = cache()->getStore()->connection()->ttl($key);

        // Handle different TTL formats:
        // - Positive seconds: Redis returns TTL in seconds (convert from -2/-1)
        // - Negative: Redis returns -1 (key doesn't exist) or -2 (expired)
        // - Large number: If > current timestamp, it's likely a Unix timestamp (Redis cluster)
        if ($ttl > 0) {
            // TTL is in seconds (correct format)
            return $ttl;
        }

        // If TTL is -1 or -2, or if large number (Unix timestamp), fallback to decay minutes
        // For Unix timestamps, calculate seconds remaining
        if ($ttl > time()) {
            // It's a Unix timestamp, calculate seconds remaining
            return max(1, $ttl - time());
        }

        // Fallback to configured decay minutes
        return max(1, $limit['decay_minutes'] * 60);
    }

    /**
     * Add rate limit headers to response
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
