<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\Product\Models\ProductAttributeValueProxy;

/**
 * Resolves single product queries with support for multiple query arguments.
 */
class SingleProductBagistoApiResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): object
    {
        if ($item instanceof \stdClass && isset($item->id)) {
            $id = is_string($item->id) ? (int) str_replace('/api/shop/products/', '', $item->id) : (int) $item->id;
            $item = Product::find($id);
            if (! $item) {
                throw new ResourceNotFoundException('No product found with ID');
            }

            return $item;
        }

        if ($item && $item instanceof Product) {
            return $item;
        }

        $args = $context['args'];

        if (! empty($args['id'])) {
            $id = $args['id'];
            if (is_string($id) && str_contains($id, '/')) {
                $parts = explode('/', trim($id, '/'));
                $id = (int) end($parts);
            }

            return Product::find($id) ?? throw new ResourceNotFoundException('No product found with ID');
        }

        if (! empty($args['sku'])) {
            return Product::where('sku', $args['sku'])->first() ?? throw new ResourceNotFoundException('No product found with SKU');
        }

        if (! empty($args['urlKey'])) {
            $productTable = (new Product)->getTable();
            $attributeValueTable = (new (ProductAttributeValueProxy::modelClass())())->getTable();

            $query = Product::query();

            $query->leftJoin("{$attributeValueTable} as pav", function ($join) use ($productTable) {
                $join->on("{$productTable}.id", '=', 'pav.product_id')
                    ->where('pav.attribute_id', '=', 3);
            })
                ->where('pav.text_value', $args['urlKey'])
                ->select("{$productTable}.*");

            return $query->get()->first() ?? throw new ResourceNotFoundException('No product found with URL key');
        }

        throw new InvalidInputException('At least one of the following parameters must be provided: "sku", "id", "urlKey"');
    }
}
