<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSessionResource;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiSession;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminSessionCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminSettingsApiSessionCollectionProvider extends AbstractAdminSessionCollectionProvider
{
    use ChecksAdminPermission;

    /**
     * Require settings.api_sessions and list every admin's sessions, or one admin's when filtered.
     */
    protected function authorizeAndScope(array $uriVariables, array $args): ?int
    {
        $this->authorizedAdmin('settings.api_sessions', 'bagistoapi::app.admin.auth.errors.no-permission');

        $adminId = (int) ($args['admin_id'] ?? $args['adminId'] ?? 0);

        return $adminId > 0
            ? $adminId
            : null;
    }

    /**
     * A new, empty API session resource.
     */
    protected function newResource(): AdminSessionResource
    {
        return new AdminSettingsApiSession;
    }
}
