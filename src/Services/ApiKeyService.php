<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Traits\HasRateLimit;

/**
 * ApiKeyService - Unified API key management
 *
 * Handles two types of API keys with clear separation:
 * - X-STOREFRONT-KEY: For shop/customer APIs (getProducts, getCart, etc)
 * - X-Admin-Key: For admin APIs (deleteProduct, updateSettings, etc)
 *
 * Benefits:
 * ✓ Clear intent - key name tells you what it can access
 * ✓ Security - separate keys can be rotated independently
 * ✓ Rate limiting - different limits per key type
 * ✓ Audit - easy to track which key type did what
 * ✓ Least privilege - apps only get keys they need
 */
class ApiKeyService
{
    use HasRateLimit;

    /**
     * API Key types and their headers
     */
    public const KEY_TYPE_SHOP = 'shop';

    public const KEY_TYPE_ADMIN = 'admin';

    protected const HEADER_NAMES = [
        self::KEY_TYPE_SHOP  => 'X-STOREFRONT-KEY',      // For shop/customer APIs
        self::KEY_TYPE_ADMIN => 'X-Admin-Key',   // For admin APIs
    ];

    /**
     * Get the API key from request headers based on key type
     *
     * @param  \Illuminate\Http\Request  $request
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
     * Get both keys from request (if present)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array ['shop' => string|null, 'admin' => string|null]
     */
    public static function getAllKeysFromRequest($request): array
    {
        return [
            self::KEY_TYPE_SHOP  => $request->header(self::HEADER_NAMES[self::KEY_TYPE_SHOP]),
            self::KEY_TYPE_ADMIN => $request->header(self::HEADER_NAMES[self::KEY_TYPE_ADMIN]),
        ];
    }

    /**
     * Validate an API key of specific type
     *
     * @param  string  $keyType  self::KEY_TYPE_SHOP or self::KEY_TYPE_ADMIN
     * @return array ['valid' => bool, 'client' => object|null, 'message' => string]
     */
    public function validate(string $key, string $keyType = self::KEY_TYPE_SHOP, string $ipAddress = ''): array
    {
        // In testing environment, allow test keys without database validation
        if (app()->environment('testing') && (str_starts_with($key, 'pk_test_') || str_starts_with($key, 'pk_admin_test_'))) {
            return [
                'valid'   => true,
                'client'  => (object)[
                    'id'         => 'test-key',
                    'name'       => 'Test Key',
                    'key_type'   => $keyType,
                    'is_active'  => true,
                    'rate_limit' => 100000,
                ],
                'message' => 'Valid',
            ];
        }

        try {
            // Try cache first
            $cacheKey = "api_key:{$keyType}:{$key}";
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return [
                    'valid'   => $cached['valid'],
                    'client'  => $cached['client'] ?? null,
                    'message' => $cached['message'] ?? 'Valid',
                ];
            }

            // Query database (try both table names for compatibility)
            $tableName = Schema::hasTable('api_keys') ? 'api_keys' : 'storefront_keys';

            $apiKey = DB::table($tableName)
                ->where('key', $key)
                ->where('key_type', $keyType)  // IMPORTANT: Check key type
                ->where('is_active', true)
                ->first();

            if (! $apiKey) {
                return [
                    'valid'   => false,
                    'client'  => null,
                    'message' => "Invalid or inactive {$keyType} API key",
                ];
            }

            // Check IP restrictions if configured
            if ($apiKey->allowed_ips && ! $this->ipAllowed($ipAddress, $apiKey->allowed_ips)) {
                return [
                    'valid'   => false,
                    'client'  => null,
                    'message' => 'IP address not allowed for this key',
                ];
            }

            // Cache result for 5 minutes
            Cache::put($cacheKey, [
                'valid'   => true,
                'client'  => $apiKey,
                'message' => 'Valid',
            ], now()->addMinutes(5));

            return [
                'valid'   => true,
                'client'  => $apiKey,
                'message' => 'Valid',
            ];
        } catch (\Exception $e) {
            return [
                'valid'   => false,
                'client'  => null,
                'message' => 'Validation error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if IP is allowed
     *
     * @param  string  $allowedIps  JSON or comma-separated list
     */
    protected function ipAllowed(string $ipAddress, string $allowedIps): bool
    {
        if (empty($allowedIps) || $ipAddress === '127.0.0.1') {
            return true;
        }

        // Parse allowed IPs (JSON array or comma-separated)
        $allowed = [];
        if (str_starts_with($allowedIps, '[')) {
            $allowed = json_decode($allowedIps, true) ?? [];
        } else {
            $allowed = array_map('trim', explode(',', $allowedIps));
        }

        return in_array($ipAddress, $allowed, true);
    }

    /**
     * Check rate limit for an API key
     *
     * Different limits per key type:
     * - Shop key: 1000/hour (or per client type)
     * - Admin key: 10000/hour (or unlimited)
     *
     * @param  object  $client  The API key object from database
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'reset_at' => int]
     */
    public function checkRateLimit($client): array
    {
        $defaultLimit = $this->getDefaultRateLimit($client->key_type ?? 'shop');

        return $this->checkHourlyRateLimit($client, $defaultLimit);
    }

    /**
     * Get default rate limit based on key type
     *
     * @return int Requests per hour
     */
    protected function getDefaultRateLimit(string $keyType): int
    {
        return match ($keyType) {
            self::KEY_TYPE_ADMIN => 10000,  // Admin: generous
            default              => 1000,                  // Shop: reasonable
        };
    }
}
