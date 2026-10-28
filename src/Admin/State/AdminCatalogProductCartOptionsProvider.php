<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCartOptions;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Helpers\BundleOption;
use Webkul\Product\Helpers\ConfigurableOption;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Mirrors the admin Simple, Virtual, Configurable, Bundle, Grouped and Downloadable
 * product option controllers the Create Order drawer calls.
 */
class AdminCatalogProductCartOptionsProvider implements ProviderInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ConfigurableOption $configurableOptionHelper,
        protected BundleOption $bundleOptionHelper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->build((int) ($uriVariables['productId'] ?? request()->route('productId')))];
    }

    public function build(int $productId): AdminCatalogProductCartOptions
    {
        AdminReadPermissions::authorize(null, null, 'catalog.products');

        $product = $productId > 0 ? $this->productRepository->find($productId) : null;

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $result = new AdminCatalogProductCartOptions;
        $result->product_id = $product->id;
        $result->type = $product->type;

        $options = match ($product->type) {
            'configurable' => $this->configurableOptionHelper->getConfigurationConfig($product),
            'bundle' => $this->bundleOptionHelper->getBundleConfig($product),
            'grouped' => $this->groupedOptions($product),
            'downloadable' => $this->downloadableOptions($product),
            'simple', 'virtual' => $this->customizableOptions($product),
            default => null,
        };

        if (in_array($product->type, ['simple', 'virtual'], true)) {
            $result->initial_price = (float) $product->getTypeInstance()->getMinimalPrice();
        }

        $result->options = $options === null ? null : json_decode(json_encode($options), true);

        return $result;
    }

    protected function groupedOptions($product): array
    {
        $products = [];

        foreach ($product->grouped_products()->orderBy('sort_order')->get() as $option) {
            if (! $option->associated_product->getTypeInstance()->isSaleable()) {
                continue;
            }

            $products[] = [
                'id' => $option->associated_product->id,
                'name' => $option->associated_product->name,
                'qty' => $option->qty,
                'price' => $price = $option->associated_product->getTypeInstance()->getFinalPrice(),
                'formatted_price' => core()->formatPrice($price),
            ];
        }

        return $products;
    }

    protected function downloadableOptions($product): array
    {
        $links = [];

        foreach ($product->downloadable_links as $link) {
            $links[] = [
                'id' => $link->id,
                'title' => $link->title,
                'price' => $link->price,
                'formatted_price' => core()->formatPrice($link->price),
            ];
        }

        return $links;
    }

    protected function customizableOptions($product): array
    {
        return $product->customizable_options()->with([
            'product',
            'customizable_option_prices',
        ])->get()->toArray();
    }
}
