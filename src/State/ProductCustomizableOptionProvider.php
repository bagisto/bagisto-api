<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductCustomizableOption;

class ProductCustomizableOptionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $uriVariables = array_map('urldecode', $uriVariables);

        // Check if we're querying nested customizable options for a specific product
        $parentResourceClass = $context['previous_operation']['class'] ?? null;
        $parentData = $context['previous_data'] ?? null;

        // If this is a nested query (part of Product query), only return the related options
        if ($parentResourceClass === 'Webkul\BagistoApi\Models\Product' && is_object($parentData)) {
            // Return only the options for this product, properly constrained
            return $parentData->customizable_options()
                ->with('customizable_option_prices') // Eager load prices
                ->orderBy('sort_order')
                ->get();
        }

        // Otherwise, handle as standalone resource
        if (isset($uriVariables['id'])) {
            return ProductCustomizableOption::findOrFail($uriVariables['id']);
        }

        return ProductCustomizableOption::query()
            ->with('customizable_option_prices')
            ->orderBy('sort_order')
            ->paginate();
    }
}
