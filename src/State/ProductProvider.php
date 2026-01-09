<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;

class ProductProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $itemProvider
    ) {}

    /**
     * Provide product with enhanced attribute values.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $result = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($result instanceof Product) {
            $this->enhanceProductWithAttributeValues($result);
        }

        if (is_iterable($result)) {
            foreach ($result as $product) {
                if ($product instanceof Product) {
                    $this->enhanceProductWithAttributeValues($product);
                }
            }
        }

        return $result;
    }

    /**
     * Load attribute values with their related attributes.
     */
    protected function enhanceProductWithAttributeValues(Product $product): void
    {
        $product->load(['attribute_values.attribute']);
    }
}
