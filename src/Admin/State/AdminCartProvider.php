<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;

class AdminCartProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        AdminReadPermissions::authorize($operation, AdminCart::class);

        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        return AdminCartPresenter::present($cart);
    }
}
