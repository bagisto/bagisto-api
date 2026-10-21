<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSession;
use Webkul\BagistoApi\Admin\Models\AdminSessionResource;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminSessionCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\RequiresAdminSession;

class AdminSessionCollectionProvider extends AbstractAdminSessionCollectionProvider
{
    use RequiresAdminSession;

    /**
     * List only the signed-in admin's own sessions, which needs a login session access token.
     */
    protected function authorizeAndScope(array $uriVariables, array $args): ?int
    {
        return (int) $this->sessionCredential()->admin->id;
    }

    /**
     * A new, empty session resource.
     */
    protected function newResource(): AdminSessionResource
    {
        return new AdminSession;
    }
}
