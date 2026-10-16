<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;

/**
 * Provider for GET /api/admin/carts/{id}. Delegates auth + ownership to
 * AdminCartGuard, then maps the cart through AdminCartPresenter.
 */
class AdminCartProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        AdminReadPermissions::authorize($operation, AdminCart::class);

        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        return AdminCartPresenter::present($cart);
    }
}
