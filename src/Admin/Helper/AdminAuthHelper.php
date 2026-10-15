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
     * Whether the admin's role grants the permission key. A role of type "all" grants everything,
     * and a stored "*" grants everything too. This is the one permission check every guard,
     * trait and provider delegates to.
     */
    public static function hasPermission(object $admin, string $permission): bool
    {
        $role = $admin->role ?? null;

        if (! $role) {
            return false;
        }

        if (($role->permission_type ?? null) === 'all') {
            return true;
        }

        $permissions = $role->permissions ?? [];

        if (is_string($permissions)) {
            $permissions = array_filter(array_map('trim', explode(',', $permissions)));
        }

        $permissions = (array) $permissions;

        return in_array($permission, $permissions, true) || in_array('*', $permissions, true);
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

        if (! $admin instanceof Admin || ! $admin->status) {
            return null;
        }

        if (method_exists($admin, 'withAccessToken')) {
            $admin->withAccessToken($row);
        } else {
            $admin->setAttribute('current_access_token', $row);
        }

        // Constrain the resolved admin's effective role to the token's abilities
        // (same as the AdminApiGuard path) so permission checks honour the token.
        $row->applyAbilityScope($admin);

        return $admin;
    }
}
