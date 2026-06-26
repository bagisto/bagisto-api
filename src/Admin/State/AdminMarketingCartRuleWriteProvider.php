<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal placeholder provider for PUT / DELETE on AdminMarketingCartRule.
 * Real lookup lives in the processor.
 */
class AdminMarketingCartRuleWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminMarketingCartRule
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return (new AdminMarketingCartRule)->forceFill([
            'id' => (int) ($uriVariables['id'] ?? 0),
        ]);
    }
}
