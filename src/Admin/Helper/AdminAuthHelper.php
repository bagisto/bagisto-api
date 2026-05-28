<?php

namespace Webkul\BagistoApi\Admin\Helper;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

/**
 * Resolves the authenticated admin behind an API request via the `admin-api`
 * guard, which validates Bearer tokens against `admin_personal_access_tokens`
 * (NOT Sanctum's `personal_access_tokens`). Sanctum customer tokens used by
 * the storefront API live in a separate table and are resolved by
 * `Auth::guard('sanctum')`.
 */
class AdminAuthHelper
{
    /**
     * Always re-resolves (no static cache) so test isolation holds when
     * multiple Bearer tokens flow through the same process.
     */
    public static function resolveAdmin(?string $token = null): ?Admin
    {
        if ($token !== null) {
            return self::resolveFromExplicitToken($token);
        }

        $user = Auth::guard('admin-api')->user();

        return $user instanceof Admin ? $user : null;
    }

    /**
     * Extract the raw Bearer token from the current request.
     */
    public static function bearerToken(): ?string
    {
        $request = Request::instance();

        return $request?->bearerToken();
    }

    /**
     * Validate an arbitrary token string (not necessarily the current
     * request's). Used by signed routes and console commands.
     */
    protected static function resolveFromExplicitToken(string $token): ?Admin
    {
        if (! str_contains($token, '|')) {
            return null;
        }

        [$id, $plain] = explode('|', $token, 2);

        if (! ctype_digit($id) || $plain === '') {
            return null;
        }

        $row = AdminPersonalAccessToken::find((int) $id);

        if (! $row || ! $row->isUsable()) {
            return null;
        }

        if (! hash_equals((string) $row->token, hash('sha256', $plain))) {
            return null;
        }

        $admin = $row->admin;

        if (! $admin instanceof Admin) {
            return null;
        }

        if (method_exists($admin, 'withAccessToken')) {
            $admin->withAccessToken($row);
        } else {
            $admin->setAttribute('current_access_token', $row);
        }

        return $admin;
    }
}
