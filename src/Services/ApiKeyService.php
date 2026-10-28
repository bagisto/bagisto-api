<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Traits\HasRateLimit;

class ApiKeyService
{
    use HasRateLimit;

    /**
     * API Key types and their headers.
     */
    public const KEY_TYPE_SHOP = 'shop';

    /**
     * The key type admin constant.
     */
    public const KEY_TYPE_ADMIN = 'admin';

    /**
     * The header names constant.
     */
    protected const HEADER_NAMES = [
        self::KEY_TYPE_SHOP => 'X-STOREFRONT-KEY',
        self::KEY_TYPE_ADMIN => 'X-Admin-Key',
    ];

    /**
     * Get the API key from request headers based on key type.
     *
     * @param  Request  $request
     * @param  string  $keyType  self::KEY_TYPE_SHOP or self::KEY_TYPE_ADMIN
     */
    public static function getKeyFromRequest($request, string $keyType = self::KEY_TYPE_SHOP): ?string
    {
        $headerName = self::HEADER_NAMES[$keyType] ?? null;

        if (! $headerName) {
            return null;
        }

        return $request->header($headerName);
    }

    /**
     * Get both keys from request (if present).
     *
     * @param  Request  $request
     * @return array ['shop' => string|null, 'admin' => string|null]
     */
    public static function getAllKeysFromRequest($request): array
    {
        return [
            self::KEY_TYPE_SHOP => $request->header(self::HEADER_NAMES[self::KEY_TYPE_SHOP]),
            self::KEY_TYPE_ADMIN => $request->header(self::HEADER_NAMES[self::KEY_TYPE_ADMIN]),
        ];
    }

    /**
     * Validate an API key of specific type.
     *
     * @param  string  $keyType  self::KEY_TYPE_SHOP or self::KEY_TYPE_ADMIN
     * @return array ['valid' => bool, 'client' => object|null, 'message' => string]
     */
    public function validate(string $key, string $keyType = self::KEY_TYPE_SHOP, string $ipAddress = ''): array
    {
        if (
            app()->environment('testing')
            && (
                str_starts_with($key, 'pk_test_')
                || str_starts_with($key, 'pk_admin_test_')
            )
        ) {
            return [
                'valid' => true,
                'client' => (object) [
                    'id' => 'test-key',
                    'name' => 'Test Key',
                    'key_type' => $keyType,
                    'is_active' => true,
                    'rate_limit' => 100000,
                ],
                'message' => 'Valid',
            ];
        }

        try {
            $cacheKey = self::cacheKey($keyType, $key);
            $cached = Cache::get($cacheKey);

            if (
                $cached
                && $this->isExpired($cached['client'] ?? null)
            ) {
                Cache::forget($cacheKey);

                $cached = null;
            }

            if ($cached) {
                $client = $cached['client'] ?? null;

                if (
                    ! empty($client->allowed_ips)
                    && ! $this->ipAllowed($ipAddress, $client->allowed_ips)
                ) {
                    return [
                        'valid' => false,
                        'client' => null,
                        'message' => 'IP address not allowed for this key',
                    ];
                }

                return [
                    'valid' => $cached['valid'],
                    'client' => $cached['client'] ?? null,
                    'message' => $cached['message'] ?? 'Valid',
                ];
            }

            $tableName = Schema::hasTable('api_keys') ? 'api_keys' : 'storefront_keys';

            $query = DB::table($tableName)
                ->where('key', $key)
                ->where('key_type', $keyType)
                ->where('is_active', true);

            if ($tableName === 'storefront_keys') {
                $query->whereNull('deleted_at')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            }

            $apiKey = $query->first();

            if (! $apiKey) {
                return [
                    'valid' => false,
                    'client' => null,
                    'message' => "Invalid or inactive {$keyType} API key",
                ];
            }

            if (
                $apiKey->allowed_ips
                && ! $this->ipAllowed($ipAddress, $apiKey->allowed_ips)
            ) {
                return [
                    'valid' => false,
                    'client' => null,
                    'message' => 'IP address not allowed for this key',
                ];
            }

            Cache::put($cacheKey, [
                'valid' => true,
                'client' => $apiKey,
                'message' => 'Valid',
            ], $this->cacheExpiry($apiKey));

            return [
                'valid' => true,
                'client' => $apiKey,
                'message' => 'Valid',
            ];
        } catch (\Exception $exception) {
            return [
                'valid' => false,
                'client' => null,
                'message' => 'Validation error: '.$exception->getMessage(),
            ];
        }
    }

    /**
     * Build the cache key that holds a validated API key.
     */
    public static function cacheKey(string $keyType, string $key): string
    {
        return "api_key:{$keyType}:{$key}";
    }

    /**
     * Drop a cached validation result so a changed key takes effect immediately.
     */
    public static function forgetCachedKey(string $keyType, string $key): void
    {
        Cache::forget(self::cacheKey($keyType, $key));
    }

    /**
     * Check the per-minute rate limit for an API key.
     * The key's `rate_limit` is requests per minute, counted separately for each.
     *
     * @param  object  $client  The API key object from database
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'reset_at' => int]
     */
    public function checkRateLimit($client, ?string $ipAddress = null): array
    {
        return $this->checkWindowRateLimit(
            $client,
            (int) config('storefront.default_rate_limit', 100),
            60,
            $ipAddress ? sha1($ipAddress) : 'no-ip',
        );
    }

    /**
     * Determine whether a key row has passed its expiry date.
     *
     * @param  object|null  $client
     */
    protected function isExpired($client): bool
    {
        if (empty($client->expires_at)) {
            return false;
        }

        return Carbon::parse($client->expires_at)->isPast();
    }

    /**
     * Cache a validation for five minutes, or until the key expires if that is sooner.
     *
     * @param  object  $client
     */
    protected function cacheExpiry($client): Carbon
    {
        $expiry = now()->addMinutes(5);

        if (empty($client->expires_at)) {
            return $expiry;
        }

        $expiresAt = Carbon::parse($client->expires_at);

        return $expiresAt->lessThan($expiry) ? $expiresAt : $expiry;
    }

    /**
     * Check if IP is allowed.
     *
     * @param  string  $allowedIps  JSON or comma-separated list
     */
    protected function ipAllowed(string $ipAddress, string $allowedIps): bool
    {
        if (empty($allowedIps)) {
            return true;
        }

        $allowed = [];
        if (str_starts_with($allowedIps, '[')) {
            $allowed = json_decode($allowedIps, true) ?? [];
        } else {
            $allowed = array_map('trim', explode(',', $allowedIps));
        }

        $allowed = array_values(array_filter($allowed));

        if (empty($allowed)) {
            return true;
        }

        return in_array($ipAddress, $allowed, true);
    }
}
