<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Support\Facades\Cache;
use Webkul\BagistoApi\Models\StorefrontKey;
use Webkul\BagistoApi\Traits\HasRateLimit;

/**
 * StorefrontKeyService
 *
 * Handles storefront key validation and rate limiting.
 * Provides centralized authentication and rate limit checking for API requests.
 */
class StorefrontKeyService
{
    use HasRateLimit;

    /**
     * Validate storefront key and check rate limits.
     *
     * @return array{valid: bool, storefront: StorefrontKey|null, error: string|null}
     */
    public function validate(string $key, ?string $ipAddress = null): array
    {
        // In testing environment, allow test keys without database validation
        if (app()->environment('testing') && (str_starts_with($key, 'pk_test_') || str_starts_with($key, 'pk_admin_test_'))) {
            return [
                'valid'      => true,
                'storefront' => new StorefrontKey([
                    'id'         => 'test-key',
                    'name'       => 'Test Key',
                    'rate_limit' => 100000,
                ]),
                'error'      => null,
            ];
        }

        $cacheKey = $this->getCacheKey($key);

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Validate key against database
        $storefront = StorefrontKey::validateKey($key, $ipAddress);

        if (! $storefront) {
            $result = [
                'valid'      => false,
                'storefront' => null,
                'error'      => 'Invalid storefront key',
            ];
            Cache::put($cacheKey, $result, now()->addMinutes(
                config('storefront.cache_ttl', 60)
            ));

            return $result;
        }

        $result = [
            'valid'      => true,
            'storefront' => $storefront,
            'error'      => null,
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(
            config('storefront.cache_ttl', 60)
        ));

        return $result;
    }

    /**
     * Check if storefront key has exceeded rate limit.
     *
     * @return array{allowed: bool, remaining: int, reset_at: int}
     */
    public function checkRateLimit(StorefrontKey $storefront): array
    {
        $limit = $storefront->rate_limit ?? config('storefront.default_rate_limit', 100);

        return $this->checkMinuteRateLimit($storefront, $limit, 1);
    }

    /**
     * Increment request count for rate limiting.
     */
    public function incrementRequestCount(StorefrontKey $storefront): void
    {
        $rateLimitKey = $this->getRateLimitKey($storefront->id);

        if (! Cache::has($rateLimitKey)) {
            Cache::put($rateLimitKey, 1, now()->addMinute());
        } else {
            Cache::increment($rateLimitKey);
        }
    }

    /**
     * Get cache key for validation result.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('storefront.key_prefix', 'storefront_key_');

        return $prefix.hash('sha256', $key);
    }

    /**
     * Get cache key for rate limiting.
     */
    protected function getRateLimitKey(int $storefrontId): string
    {
        return 'storefront_rate_limit_'.$storefrontId;
    }
}
