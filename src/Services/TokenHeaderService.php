<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Http\Request;

class TokenHeaderService
{
    /**
     * Extract Bearer token from Authorization header
     * Format: Authorization: Bearer <token>.
     */
    public static function extractToken(Request $request): ?string
    {
        return self::getAuthorizationBearerToken($request);
    }

    /**
     * Get Authorization Bearer token from request header
     * Format: Authorization: Bearer <token>.
     */
    public static function getAuthorizationBearerToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (
            $authHeader
            && str_starts_with($authHeader, 'Bearer ')
        ) {
            return substr($authHeader, 7);
        }

        return null;
    }

    /**
     * Check if Authorization Bearer token is present.
     */
    public static function hasAuthorizationToken(Request $request): bool
    {
        return ! empty(self::getAuthorizationBearerToken($request));
    }
}
