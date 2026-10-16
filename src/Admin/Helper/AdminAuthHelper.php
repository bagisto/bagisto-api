<?php

namespace Webkul\BagistoApi\Admin\Helper;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

class AdminAuthHelper
{
    /**
     * Resolve the admin from an explicit token or the `admin-api` guard, re-resolving on every call without a cache.
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
     * Whether the admin's role grants the permission key; a role of type "all" or a stored "*" grants everything.
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
     * Whether the admin may grant the given role, which must hold no permission they lack.
     */
    public static function canGrantRole(?object $admin, ?object $role): bool
    {
        return (bool) $role
            && self::canGrantPermissions($admin, $role->permission_type, (array) $role->permissions);
    }

    /**
     * Whether the admin may grant the given permission type and permissions, as core's Bouncer decides it.
     */
    public static function canGrantPermissions(?object $admin, ?string $permissionType, array $permissions): bool
    {
        $role = $admin?->role;

        if (! $role) {
            return false;
        }

        if ($role->permission_type === 'all') {
            return true;
        }

        return $permissionType !== 'all'
            && empty(array_diff($permissions, (array) $role->permissions));
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
     * Resolve the admin behind an arbitrary token string, scoping their role to the token's abilities.
     */
    protected static function resolveFromExplicitToken(string $token): ?Admin
    {
        if (! str_contains($token, '|')) {
            return null;
        }

        [$id, $plain] = explode('|', $token, 2);

        if (
            ! ctype_digit($id)
            || $plain === ''
        ) {
            return null;
        }

        $row = AdminPersonalAccessToken::find((int) $id);

        if (
            ! $row
            || ! $row->isUsable()
        ) {
            return null;
        }

        if (! hash_equals((string) $row->token, hash('sha256', $plain))) {
            return null;
        }

        $admin = $row->admin;

        if (
            ! $admin instanceof Admin
            || ! $admin->status
        ) {
            return null;
        }

        if (method_exists($admin, 'withAccessToken')) {
            $admin->withAccessToken($row);
        } else {
            $admin->setAttribute('current_access_token', $row);
        }

        $row->applyAbilityScope($admin);

        return $admin;
    }
}
