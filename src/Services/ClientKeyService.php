<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Traits\HasRateLimit;

class ClientKeyService
{
    use HasRateLimit;

    /**
     * Standard header name for API key authentication
     * Works for all client types: mobile, web, headless, etc.
     */
    protected const HEADER_NAME = 'X-STOREFRONT-KEY';

    /**
     * Get the API key from request headers.
     *
     * @param  Request  $request
     */
    public static function getKeyFromRequest($request): ?string
    {
        return $request->header(self::HEADER_NAME);
    }

    /**
     * Validate a client key.
     *
     * @return array ['valid' => bool, 'client' => ClientKey|null, 'message' => string]
     */
    public function validate(string $key, string $ipAddress = ''): array
    {
        try {
            $cacheKey = "client_key:{$key}";
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return [
                    'valid' => $cached['valid'],
                    'client' => $cached['client'] ?? null,
                    'message' => $cached['message'] ?? 'Valid',
                ];
            }

            $tableName = Schema::hasTable('client_keys') ? 'client_keys' : 'storefront_keys';

            $clientKey = DB::table($tableName)
                ->where('key', $key)
                ->where('is_active', true)
                ->first();

            if (! $clientKey) {
                return [
                    'valid' => false,
                    'client' => null,
                    'message' => 'Invalid or inactive key',
                ];
            }

            if (
                $clientKey->allowed_ips
                && ! $this->ipAllowed($ipAddress, $clientKey->allowed_ips)
            ) {
                return [
                    'valid' => false,
                    'client' => null,
                    'message' => 'IP address not allowed for this key',
                ];
            }

            Cache::put($cacheKey, [
                'valid' => true,
                'client' => $clientKey,
                'message' => 'Valid',
            ], now()->addMinutes(5));

            return [
                'valid' => true,
                'client' => $clientKey,
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
     * Check rate limit for a client.
     *
     * @param  object  $client  The client object from database
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'reset_at' => int]
     */
    public function checkRateLimit($client): array
    {
        return $this->checkHourlyRateLimit($client, 1000);
    }

    /**
     * Check if IP is allowed.
     *
     * @param  string  $allowedIps  JSON or comma-separated list
     */
    protected function ipAllowed(string $ipAddress, string $allowedIps): bool
    {
        if (
            empty($allowedIps)
            || $ipAddress === '127.0.0.1'
        ) {
            return true;
        }

        $allowed = [];
        if (str_starts_with($allowedIps, '[')) {
            $allowed = json_decode($allowedIps, true) ?? [];
        } else {
            $allowed = array_map('trim', explode(',', $allowedIps));
        }

        return in_array($ipAddress, $allowed, true);
    }
}
