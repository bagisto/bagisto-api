<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttributeOption;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal item provider for AdminAttributeOption PUT/DELETE operations.
 *
 * API Platform requires a provider on PUT and DELETE operations so it can
 * resolve the resource before passing it to the processor. Since option
 * mutations are fully processor-driven (they read uriVariables, not $data),
 * this provider just returns a placeholder AdminAttributeOption instance
 * so the framework doesn't short-circuit with 404.
 *
 * Auth is re-checked here (consistent with other admin item providers) and
 * again in the processor (belt-and-suspenders).
 */
class AdminAttributeOptionProvider implements ProviderInterface
{
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
