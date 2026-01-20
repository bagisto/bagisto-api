<?php

namespace Webkul\BagistoApi\Resolver;

use Webkul\BagistoApi\Models\Product;

/**
 * Generates the configurable product option index for headless developers.
 * Similar to Shop package's ConfigurableOption helper.
 * 
 * Returns an index mapping: variant_id -> attribute_id -> option_value
 * This allows frontend to identify which variant is selected based on option values.
 */
class ConfigurableOptionIndexResolver
{
    /**
     * Get the configurable option index for a product.
     * 
     * Index structure:
     * {
     *   "588": {
     *     "23": "1",      // attribute_id 23 (color) has value 1 (red)
     *     "24": "6"       // attribute_id 24 (size) has value 6 (medium)
     *   },
     *   "589": {
     *     "23": "2",
     *     "24": "6"
     *   }
     * }
     */
    public function getConfigurableIndex(Product $product): array
    {
        if ($product->type !== 'configurable') {
            return [];
        }

        $index = [];

        // Load super attributes if not already loaded
        if (! $product->relationLoaded('super_attributes')) {
            $product->load('super_attributes');
        }

        // Load variants with attribute values if not already loaded
        if (! $product->relationLoaded('variants')) {
            $product->load([
                'variants' => function ($query) {
                    $query->with(['attribute_values.attribute']);
                }
            ]);
        }

        // Get super attribute IDs
        $superAttributeIds = $product->super_attributes->pluck('id')->toArray();

        if (empty($superAttributeIds)) {
            return [];
        }

        // Build index: variant_id -> attribute_id -> attribute_value
        foreach ($product->variants as $variant) {
            if (! isset($index[$variant->id])) {
                $index[$variant->id] = [];
            }

            // Load variant's attribute values if needed
            if (! $variant->relationLoaded('attribute_values')) {
                $variant->load('attribute_values.attribute');
            }

            // Get the attribute value for each super attribute
            foreach ($variant->attribute_values as $attrValue) {
                // Only include super attributes
                if (in_array($attrValue->attribute_id, $superAttributeIds)) {
                    $index[$variant->id][$attrValue->attribute_id] = $attrValue->value;
                }
            }
        }

        return $index;
    }
}
