<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductCustomizableOptionPrice;

class ProductCustomizableOptionPriceProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $uriVariables = array_map('urldecode', $uriVariables);

        $parentResourceClass = $context['previous_operation']['class'] ?? null;
        $parentData = $context['previous_data'] ?? null;

        if (
            $parentResourceClass === 'Webkul\BagistoApi\Models\ProductCustomizableOption'
            && is_object($parentData)
        ) {
            return $parentData->customizable_option_prices()
                ->orderBy('sort_order')
                ->get();
        }

        if (isset($uriVariables['id'])) {
            return ProductCustomizableOptionPrice::findOrFail($uriVariables['id']);
        }

        return ProductCustomizableOptionPrice::query()
            ->orderBy('sort_order')
            ->paginate();
    }
}
