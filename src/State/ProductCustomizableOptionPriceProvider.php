<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductCustomizableOptionPrice;

class ProductCustomizableOptionPriceProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $uriVariables = array_map('urldecode', $uriVariables);

        // If querying nested prices for a specific option
        $parentResourceClass = $context['previous_operation']['class'] ?? null;
        $parentData = $context['previous_data'] ?? null;

        if ($parentResourceClass === 'Webkul\BagistoApi\Models\ProductCustomizableOption' && is_object($parentData)) {
            // Return only prices for this customizable option
            return $parentData->customizable_option_prices()
                ->orderBy('sort_order')
                ->get();
        }

        // Standalone resource query
        if (isset($uriVariables['id'])) {
            return ProductCustomizableOptionPrice::findOrFail($uriVariables['id']);
        }

        return ProductCustomizableOptionPrice::query()
            ->orderBy('sort_order')
            ->paginate();
    }
}
