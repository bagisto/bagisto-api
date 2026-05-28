<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductImage;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal item provider for AdminCatalogProductImage PUT (reorder) and DELETE.
 *
 * Mirrors AdminAttributeFamilyWriteProvider: API Platform requires a provider
 * on PUT/DELETE so it can resolve the resource shell before passing it to the
 * processor. Real entity lookup, validation and ownership checks live in the
 * processor.
 */
class AdminCatalogProductImageProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCatalogProductImage
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCatalogProductImage;
        $placeholder->id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $placeholder->productId = isset($uriVariables['productId']) ? (int) $uriVariables['productId'] : null;

        return $placeholder;
    }
}
