<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider for AdminSettingsTaxCategory PUT/DELETE so API Platform
 * can resolve the resource before dispatching to the processor.
 */
class AdminSettingsTaxCategoryWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsTaxCategory
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminSettingsTaxCategory;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
