<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerAddress;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminCustomerAddressWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCustomerAddress
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCustomerAddress;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);
        $placeholder->customerId = (int) ($uriVariables['customerId'] ?? 0);

        return $placeholder;
    }
}
