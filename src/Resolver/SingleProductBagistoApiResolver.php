<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
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
     *
     * @throws ResourceNotFoundException|InvalidInputException
     */
    public function __invoke(?object $item, array $context): object
    {
        $args = $context['args'] ?? [];

        /** Resolve locale/channel from args first, then headers, then defaults */
        $locale = $args['locale'] ?? request()->attributes->get('bagisto_locale');
        $channel = $args['channel'] ?? request()->attributes->get('bagisto_channel');

        if ($item instanceof \stdClass && isset($item->id)) {
            $product = $this->resolveById($item->id);

            return $this->applyLocaleChannel($product, $locale, $channel);
        }

        if (! empty($args['id'])) {
            $product = parent::__invoke($item, $context);

            return $this->applyLocaleChannel($product, $locale, $channel);
        }

        if (! empty($args['sku'])) {
            $product = $this->resolveBySku($args['sku']);

            return $this->applyLocaleChannel($product, $locale, $channel);
        }

        if (! empty($args['urlKey'])) {
            $product = $this->resolveByUrlKey($args['urlKey']);

            return $this->applyLocaleChannel($product, $locale, $channel);
        }

        throw new InvalidInputException(__('bagistoapi::app.graphql.product.missing-query-parameter'));
    }

    /**
     * Set locale and channel context on the product for attribute value resolution.
     */
    private function applyLocaleChannel(Product $product, ?string $locale, ?string $channel): Product
    {
        if ($locale) {
            $product->locale = $locale;
        }

        if ($channel) {
            $product->channel = $channel;
        }

        return $product;
    }

    /**
     * Resolve product by numeric ID.
     *
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
