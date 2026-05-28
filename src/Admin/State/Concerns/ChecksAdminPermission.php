<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * Shared admin auth + permission check for read-only Sales / Settings
 * providers. Mirrors the Sanctum-token resolution pattern used by every
 * admin endpoint: NEVER call bouncer() — read $admin->role->permissions
 * directly from the API-resolved admin.
 */
trait ChecksAdminPermission
{
    /**
     * Resolve the admin from the Bearer token and assert they hold the
     * given permission. Returns the admin model on success.
     *
     * @throws AuthenticationException When no admin is resolved (401).
     * @throws AuthorizationException When the admin lacks the permission (403).
     */
    protected function authorizedAdmin(string $permission, string $noPermLangKey = 'bagistoapi::app.admin.sales.no-permission'): object
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $this->adminHasPermission($admin, $permission)) {
            throw new AuthorizationException(__($noPermLangKey));
        }

        return $admin;
    }

    protected function adminHasPermission(object $admin, string $permission): bool
    {
        $role = $admin->role ?? null;

        if (! $role) {
            return false;
        }

        if (($role->permission_type ?? null) === 'all') {
            return true;
        }

        $perms = $role->permissions ?? [];

        if (is_string($perms)) {
            $perms = array_filter(array_map('trim', explode(',', $perms)));
        }

        $perms = (array) $perms;

        return in_array($permission, $perms, true) || in_array('*', $perms, true);
    }
}
