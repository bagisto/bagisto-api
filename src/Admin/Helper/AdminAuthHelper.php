<?php

namespace Webkul\BagistoApi\Admin\Helper;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Admin\Auth\AdminApiGuard;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

/**
 * Resolves the authenticated admin behind an API request.
 *
 * As of the 2026-05-27 refactor, admin endpoints authenticate via the
 * `admin-api` guard (Webkul\BagistoApi\Admin\Auth\AdminApiGuard) which validates
 * Bearer tokens against the `admin_personal_access_tokens` table
 * (NOT Sanctum's `personal_access_tokens`). This helper is the single place
 * that turns the current Bearer token into an Admin model — used by every
 * /api/admin/* provider, processor, and resolver.
 *
 * Sanctum customer tokens (used by the storefront API) are unaffected; they
 * live in a separate table and are resolved by `Auth::guard('sanctum')`.
 */
class AdminAuthHelper
{
    /**
     * Resolve the admin from the current request's Bearer token via the
     * admin-api guard. Always re-resolves (no static cache) so test isolation
     * holds when multiple Bearer tokens flow through the same process.
     */
    public static function resolveAdmin(?string $token = null): ?Admin
    {
        if ($token !== null) {
            return self::resolveFromExplicitToken($token);
        }

        // The standard path: a Bearer header is on the request, the
        // admin-api guard (AdminApiGuard) parses + validates it against
        // admin_personal_access_tokens, returns the Admin model.
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
     * request's). Used by edge paths that already have the token in hand
     * (e.g. signed routes, console commands).
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
