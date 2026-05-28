<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider for PUT / DELETE on AdminCustomer.
 */
class AdminCustomerWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCustomer
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCustomer;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
