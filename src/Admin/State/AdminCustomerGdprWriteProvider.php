<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider for PUT / DELETE on AdminCustomerGdprRequest.
 * Auth runs in the processor; this just gives API Platform a non-null
 * resource so the route resolves before the processor takes over.
 */
class AdminCustomerGdprWriteProvider implements ProviderInterface
{
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
