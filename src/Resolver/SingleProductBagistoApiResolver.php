<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\Product\Models\ProductAttributeValueProxy;

/**
 * Resolves single product queries by ID, SKU, or URL key.
 */
class SingleProductBagistoApiResolver extends BaseQueryItemResolver implements QueryItemResolverInterface
{
    /**
     * Resolves product queries based on provided arguments.
     *
     * @param  ?object  $item
     * @param  array  $context
     * @return object
     *
     * @throws ResourceNotFoundException|InvalidInputException
     */
    public function __invoke(?object $item, array $context): object
    {
        if ($item instanceof \stdClass && isset($item->id)) {
            return $this->resolveById($item->id);
        }

        $args = $context['args'] ?? [];

        if (! empty($args['id'])) {
            return parent::__invoke($item, $context);
        }

        if (! empty($args['sku'])) {
            return $this->resolveBySku($args['sku']);
        }

        if (! empty($args['urlKey'])) {
            return $this->resolveByUrlKey($args['urlKey']);
        }

        throw new InvalidInputException(__('bagistoapi::app.graphql.product.missing-query-parameter'));
    }

    /**
     * Resolve product by numeric ID.
     *
     * @param  int|string  $id
     * @return Product
     *
     * @throws ResourceNotFoundException
     */
    private function resolveById(int|string $id): Product
    {
        $numericId = is_string($id) ? (int) str_replace('/api/shop/products/', '', $id) : (int) $id;

        $product = Product::find($numericId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return $product;
    }

    /**
     * Resolve product by SKU.
     *
     * @param  string  $sku
     * @return Product
     *
     * @throws ResourceNotFoundException
     */
    private function resolveBySku(string $sku): Product
    {
        return Product::where('sku', $sku)
            ->first() ?? throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.product.not-found-with-sku')
            );
    }

    /**
     * Resolve product by URL key attribute.
     *
     * @param  string  $urlKey
     * @return Product
     *
     * @throws ResourceNotFoundException
     */
    private function resolveByUrlKey(string $urlKey): Product
    {
        $productTable = Product::make()->getTable();
        $attributeValueTable = (new (ProductAttributeValueProxy::modelClass())())->getTable();

        $product = Product::query()
            ->leftJoin("{$attributeValueTable} as pav", function ($join) use ($productTable) {
                $join->on("{$productTable}.id", '=', 'pav.product_id')
                    ->where('pav.attribute_id', 3);
            })
            ->where('pav.text_value', $urlKey)
            ->select("{$productTable}.*")
            ->first();

        if (! $product) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.product.not-found-with-url-key')
            );
        }

        return $product;
    }
}
