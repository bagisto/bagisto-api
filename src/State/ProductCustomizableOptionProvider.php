<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductCustomizableOption;

class ProductCustomizableOptionProvider implements ProviderInterface
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
            $parentResourceClass === 'Webkul\BagistoApi\Models\Product'
            && is_object($parentData)
        ) {
            return $parentData->customizable_options()
                ->with('customizable_option_prices')
                ->orderBy('sort_order')
                ->get();
        }

        if (isset($uriVariables['id'])) {
            return ProductCustomizableOption::findOrFail($uriVariables['id']);
        }

        return ProductCustomizableOption::query()
            ->with('customizable_option_prices')
            ->orderBy('sort_order')
            ->paginate();
    }
}
