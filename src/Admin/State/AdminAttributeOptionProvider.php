<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttributeOption;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminAttributeOptionProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminAttributeOption
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminAttributeOption;
        $placeholder->id = (int) ($uriVariables['optionId'] ?? $uriVariables['id'] ?? 0);
        $placeholder->attributeId = (int) ($uriVariables['attributeId'] ?? 0);

        return $placeholder;
    }
}
