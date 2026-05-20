<?php

namespace Webkul\BagistoApi\Admin\Helper;

use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\User\Models\Admin;

/**
 * Resolves the authenticated admin behind an API request.
 *
 * Admin API tokens are Sanctum personal access tokens issued by the
 * `/api/admin/login` endpoint on the `Webkul\User\Models\Admin` model
 * (tokenable_type = Admin). This helper is the single place that turns a
 * Bearer token into an Admin model — used by the logout / profile / update
 * processors and the GraphQL resolver.
 *
 * NOTE: this is distinct from the admin *integration* tokens
 * (`admin_personal_access_tokens` + `AdminApiGuard`), which are managed via
 * the admin-panel Integration plugin and used by server-to-server callers.
 */
class AdminAuthHelper
{
    /**
     * Resolve the admin from the current request's Bearer token.
     *
     * Resolution is always done fresh from the token (not via
     * Auth::guard('sanctum')->user(), which caches a stale user across
     * requests within a single test run).
     */
    public static function resolveAdmin(?string $token = null): ?Admin
    {
        $token = $token ?: self::bearerToken();

        if (! $token || ! str_contains($token, '|')) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            return null;
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        $tokenable = $accessToken->tokenable;

        if (! $tokenable instanceof Admin) {
            return null;
        }

        $tokenable->withAccessToken($accessToken);

        return $tokenable;
    }

    /**
     * Extract the raw Bearer token from the current request.
     */
    public static function bearerToken(): ?string
    {
        $request = Request::instance();

        return $request?->bearerToken();
    }
}
