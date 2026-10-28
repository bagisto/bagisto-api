<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSessionResource;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUserSession;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminSessionCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\User\Repositories\AdminRepository;

class AdminSettingsUserSessionCollectionProvider extends AbstractAdminSessionCollectionProvider
{
    use ChecksAdminPermission;

    /**
     * Create a new admin user sessions provider.
     */
    public function __construct(
        AdminApiSessionRepository $sessionRepository,
        protected AdminRepository $adminRepository,
    ) {
        parent::__construct($sessionRepository);
    }

    /**
     * Require settings.api_sessions and list the sessions of the admin user the path or argument names.
     */
    protected function authorizeAndScope(array $uriVariables, array $args): ?int
    {
        $this->authorizedAdmin('settings.api_sessions', 'bagistoapi::app.admin.auth.errors.no-permission');

        $id = (int) ($uriVariables['id'] ?? $args['admin_id'] ?? $args['adminId'] ?? 0);

        if (
            $id <= 0
            || ! $this->adminRepository->find($id)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        return $id;
    }

    /**
     * A new, empty admin user session resource.
     */
    protected function newResource(): AdminSessionResource
    {
        return new AdminSettingsUserSession;
    }
}
