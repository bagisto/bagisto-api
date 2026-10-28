<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminCustomerGdprWriteProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCustomerGdprRequest
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCustomerGdprRequest;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
