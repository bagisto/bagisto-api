<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider for AdminMarketingCatalogRule PUT/DELETE so API Platform
 * can resolve the resource before dispatching to the processor.
 */
class AdminMarketingCatalogRuleWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminMarketingCatalogRule
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return (new AdminMarketingCatalogRule)->forceFill([
            'id' => (int) ($uriVariables['id'] ?? 0),
        ]);
    }
}
