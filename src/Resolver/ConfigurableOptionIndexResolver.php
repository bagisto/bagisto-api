<?php

namespace Webkul\BagistoApi\Resolver;

use Webkul\BagistoApi\Models\Product;

class ConfigurableOptionIndexResolver
{
    /**
     * Get the configurable option index for a product.
     * Index structure:
     */
    public function getConfigurableIndex(Product $product): array
    {
        if ($product->type !== 'configurable') {
            return [];
        }

        $index = [];

        if (! $product->relationLoaded('super_attributes')) {
            $product->load('super_attributes');
        }

        if (! $product->relationLoaded('variants')) {
            $product->load([
                'variants' => function ($query) {
                    $query->with(['attribute_values.attribute']);
                },
            ]);
        }

        $superAttributeIds = $product->super_attributes->pluck('id')->toArray();

        if (empty($superAttributeIds)) {
            return [];
        }

        foreach ($product->variants as $variant) {
            if (! isset($index[$variant->id])) {
                $index[$variant->id] = [];
            }

            if (! $variant->relationLoaded('attribute_values')) {
                $variant->load('attribute_values.attribute');
            }

            foreach ($variant->attribute_values as $attrValue) {
                if (in_array($attrValue->attribute_id, $superAttributeIds)) {
                    $index[$variant->id][$attrValue->attribute_id] = $attrValue->value;
                }
            }
        }

        return $index;
    }
}
