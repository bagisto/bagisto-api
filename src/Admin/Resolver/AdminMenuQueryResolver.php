<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMenu;
use Webkul\BagistoApi\Admin\State\AdminMenuProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminMenuQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminMenuProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminMenu
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthorizationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return AdminMenuProvider::toDto(AdminMenuProvider::buildPayload($admin, $this->provider));
    }
}
