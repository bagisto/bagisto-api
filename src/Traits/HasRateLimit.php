<?php

namespace Webkul\BagistoApi\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Provides centralized rate limiting for API services.
 */
trait HasRateLimit
{
    /**
     * Unlimited rate limit indicator.
     */
    protected const UNLIMITED_RATE = -1;

    /**
     * Check hourly rate limit for a client.
     *
     * @param  object  $client
     * @param  int  $defaultLimit
     * @return array
     */
    protected function checkHourlyRateLimit($client, int $defaultLimit = 1000): array
    {
        $rateLimit = $client->rate_limit ?? $defaultLimit;

        if ($rateLimit === null) {
            return [
                'allowed'   => true,
                'limit'     => self::UNLIMITED_RATE,
                'remaining' => self::UNLIMITED_RATE,
                'reset_at'  => 0,
                'unlimited' => true,
            ];
        }

        $now = now()->timestamp;
        $hour = floor($now / 3600) * 3600;
        $nextHour = $hour + 3600;

        $cacheKey = "rate_limit:{$client->id}:{$hour}";
        $used = Cache::get($cacheKey, 0);

        $allowed = $used < $rateLimit;
        Cache::put($cacheKey, $used + 1, now()->addHour());

        return [
            'allowed'   => $allowed,
            'limit'     => $rateLimit,
            'remaining' => max(0, $rateLimit - $used - 1),
            'reset_at'  => max(1, $nextHour - $now),
            'unlimited' => false,
        ];
    }

    /**
     * Check per-minute rate limit for a client.
     *
     * @param  object  $client
     * @param  int  $rateLimit
     * @param  int  $windowMinutes
     * @return array
     */
    protected function checkMinuteRateLimit($client, int $rateLimit = 100, int $windowMinutes = 1): array
    {
        $limit = $client->rate_limit ?? $rateLimit;

        if ($limit === null) {
            return [
                'allowed'   => true,
                'remaining' => self::UNLIMITED_RATE,
                'reset_at'  => 0,
                'unlimited' => true,
            ];
        }

        $cacheKey = "rate_limit:{$client->id}:minute";

        $requests = Cache::get($cacheKey, 0);
        $allowed = $requests < $limit;
        $remaining = max(0, $limit - $requests);

        if (! Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, now()->addMinutes($windowMinutes));
            $resetAt = $windowMinutes * 60;
        } else {
            Cache::increment($cacheKey);
            $resetAt = $this->calculateReset($cacheKey, $windowMinutes * 60);
        }

        return [
            'allowed'   => $allowed,
            'remaining' => $remaining,
            'reset_at'  => $resetAt,
            'unlimited' => false,
        ];
    }

    /**
     * Calculate reset time for cache TTL.
     *
     * @param  string  $cacheKey
     * @param  int  $defaultSeconds
     * @return int
     */
    private function calculateReset(string $cacheKey, int $defaultSeconds = 60): int
    {
        try {
            $ttl = Cache::getStore()->connection()->ttl($cacheKey);

            if ($ttl > 0 && $ttl <= $defaultSeconds) {
                return $ttl;
            }

            if ($ttl > $defaultSeconds && $ttl > time()) {
                return max(1, $ttl - time());
            }

            return max(1, $defaultSeconds);
        } catch (\Exception) {
            return max(1, $defaultSeconds);
        }
    }
}
