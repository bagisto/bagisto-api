<?php

namespace Webkul\BagistoApi\Service;

use Webkul\Product\Helpers\ConfigurableOption;
use Webkul\Product\Repositories\ProductRepository;

/**
 * A configurable product's option selector, built by the same helper the storefront product page uses.
 *
 * Only saleable variants count, options keep their admin sort order and labels are translated, so
 * an app renders exactly what the web shows.
 */
class ConfigurableConfig
{
    /**
     * Configurations already built in this request, keyed by product id.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $configs = [];

    /**
     * The storefront's configuration for the product, or an empty array when it is not configurable.
     *
     * Built from the core product model, as the web page does: the API's own model overrides the
     * attribute accessors the helper reads each variant's option through.
     *
     * @return array<string, mixed>
     */
    public function for(object $product): array
    {
        if ($product->type !== 'configurable') {
            return [];
        }

        return $this->configs[$product->id] ??= app()->make(ConfigurableOption::class)->getConfigurationConfig(
            app(ProductRepository::class)->find($product->id)
        );
    }

    /**
     * Each saleable variant's options as `variantId => [attributeCode => optionId]`.
     *
     * @return array<int, array<string, int>>
     */
    public function index(object $product): array
    {
        $config = $this->for($product);

        $codes = array_column($config['attributes'] ?? [], 'code', 'id');

        $index = [];

        foreach ($config['index'] ?? [] as $variantId => $options) {
            foreach ($options as $attributeId => $optionId) {
                if (isset($codes[$attributeId])) {
                    $index[(int) $variantId][$codes[$attributeId]] = (int) $optionId;
                }
            }
        }

        return $index;
    }

    /**
     * The options each super attribute offers, in the storefront's order, as `attributeId => [optionId => option]`.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function options(object $product): array
    {
        $options = [];

        foreach ($this->for($product)['attributes'] ?? [] as $attribute) {
            foreach ($attribute['options'] as $option) {
                $options[(int) $attribute['id']][(int) $option['id']] = $option;
            }
        }

        return $options;
    }

    /**
     * The attribute's options the storefront offers, as models in the storefront's order.
     *
     * @return array<int, object>
     */
    public function optionModels(object $product, object $attribute): array
    {
        $models = $attribute->options->keyBy('id');

        return array_values(array_filter(array_map(
            fn (int $optionId) => $models->get($optionId),
            array_keys($this->options($product)[$attribute->id] ?? [])
        )));
    }
}
