<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminCart;

/**
 * Provider for GET /api/admin/carts/{id}. Delegates auth + ownership to
 * AdminCartGuard, then maps the cart through AdminCartPresenter.
 */
class AdminCartProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        return AdminCartPresenter::present($cart);
    }
}
