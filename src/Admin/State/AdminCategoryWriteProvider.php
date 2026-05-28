<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCategory;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal item provider for AdminCategory PUT and DELETE operations.
 *
 * API Platform requires a provider on PUT/DELETE so it can resolve the resource
 * before passing it to the processor. The actual entity lookup and validation
 * lives inside the processor (which reads from $uriVariables / context).
 */
class AdminCategoryWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCategory
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCategory;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
