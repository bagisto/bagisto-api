<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductVideo;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminCatalogProductVideoProvider implements ProviderInterface
{
    /**
     * Provide a placeholder video resource carrying the ids from the route.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminCatalogProductVideo;
        $placeholder->id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        $placeholder->product_id = isset($uriVariables['productId']) ? (int) $uriVariables['productId'] : null;

        return $placeholder;
    }
}
