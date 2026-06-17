<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminPermissions;
use Webkul\BagistoApi\Admin\State\AdminPermissionsProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminPermissionsQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): AdminPermissions
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthorizationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return AdminPermissionsProvider::toDto(AdminPermissionsProvider::buildPayload($admin));
    }
}
