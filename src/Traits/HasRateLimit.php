<?php

namespace Webkul\BagistoApi\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * HasRateLimit Trait
 *
 * Centralized rate limiting logic for API services.
 * Handles both minute-based and hour-based rate limits.
 *
 * Provides:
 * - Consistent rate limit checking across all services
 * - Proper TTL handling (seconds vs Unix timestamps)
 * - Cache management for request counting
 *
 * Usage in services:
 * ```php
 * class MyService {
 *     use HasRateLimit;
 *
 *     public function checkLimit($client) {
 *         return $this->checkHourlyRateLimit($client);
 *         // or
 *         return $this->checkMinuteRateLimit($client);
 *     }
 * }
 * ```
 */
trait HasRateLimit
{
    /**
     * Check hourly rate limit for a client/key
     *
     * Used for: ApiKeyService, ClientKeyService (1000/hour, 10000/hour)
     *
     * @param  object  $client  Object with id and rate_limit properties
     * @param  int  $defaultLimit  Default limit if not set on client
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'reset_at' => int]
     */
    protected function checkHourlyRateLimit($client, int $defaultLimit = 1000): array
    {
        $rateLimit = $client->rate_limit ?? $defaultLimit;
        $now = now()->timestamp;
        $hour = floor($now / 3600) * 3600;
        $nextHour = $hour + 3600;

        $cacheKey = "rate_limit:{$client->id}:{$hour}";
        $used = Cache::get($cacheKey, 0);

        $allowed = $used < $rateLimit;

        // Increment counter
        Cache::put($cacheKey, $used + 1, now()->addHour());

        return [
            'allowed'   => $allowed,
            'limit'     => $rateLimit,
            'remaining' => max(0, $rateLimit - $used - 1),
            'reset_at'  => max(1, $nextHour - $now), // Return seconds remaining
        ];
    }

    /**
     * Check per-minute rate limit for a key/storefront
     *
     * Used for: StorefrontKeyService (100/minute or configured)
     *
     * @param  object  $client  Object with id and rate_limit properties
     * @param  int  $defaultLimit  Default limit if not set on client
     * @param  int  $windowMinutes  Rate limit window in minutes (default: 1)
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
     */
    protected function checkMinuteRateLimit($client, int $defaultLimit = 100, int $windowMinutes = 1): array
    {
        $rateLimit = $client->rate_limit ?? $defaultLimit;
        $cacheKey = "rate_limit:{$client->id}:minute";

        $requests = Cache::get($cacheKey, 0);
        $allowed = $requests < $rateLimit;
        $remaining = max(0, $rateLimit - $requests);

        // Initialize cache on first request
        if (! Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, now()->addMinutes($windowMinutes));
            $resetAt = $windowMinutes * 60;
        } else {
            // Increment counter and get actual TTL
            Cache::increment($cacheKey);
            $resetAt = $this->calculateReset($cacheKey, $windowMinutes * 60);
        }

        return [
            'allowed'   => $allowed,
            'remaining' => $remaining,
            'reset_at'  => $resetAt,
        ];
    }

    /**
     * Calculate reset time handling different TTL formats
     *
     * Handles:
     * - Standard seconds (Redis returns -1 for missing, positive for seconds)
     * - Unix timestamps (Redis Cluster returns timestamps)
     * - Expired keys
     *
     * @param  string  $cacheKey  The cache key to check TTL for
     * @param  int  $defaultSeconds  Fallback TTL in seconds
     * @return int Seconds remaining until reset
     */
    private function calculateReset(string $cacheKey, int $defaultSeconds = 60): int
    {
        try {
            $ttl = Cache::getStore()->connection()->ttl($cacheKey);

            // Standard case: Redis returns positive seconds remaining
            if ($ttl > 0 && $ttl <= $defaultSeconds) {
                return $ttl;
            }

            // Unix timestamp case: Redis returns a timestamp instead of TTL
            // Check if value is a timestamp (much larger than expected TTL)
            if ($ttl > $defaultSeconds && $ttl > time()) {
                return max(1, $ttl - time());
            }

            // Default fallback
            return max(1, $defaultSeconds);
        } catch (\Exception $e) {
            // If TTL check fails, return default
            return max(1, $defaultSeconds);
        }
    }
}
