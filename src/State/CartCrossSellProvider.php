<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\ProductDetail\ProductSummaryDto;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Product\Repositories\ProductRepository;

class CartCrossSellProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Provide configured cross-sell products for the current cart.
     *
     * @return ProductSummaryDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        $productIds = $cart->items->pluck('product_id')->all();

        if (! $productIds) {
            return [];
        }

        $crossSellIds = DB::table('product_cross_sells')
            ->whereIn('parent_id', $productIds)
            ->whereNotIn('child_id', $productIds)
            ->distinct()
            ->pluck('child_id');

        return $this->productRepository
            ->whereIn('products.id', $crossSellIds)
            ->take(core()->getConfigData('catalog.products.cart_view_page.no_of_cross_sells_products'))
            ->get()
            ->map(fn ($product) => new ProductSummaryDto(
                id: (int) $product->id,
                sku: $product->sku,
                name: $product->name,
                price: $product->price !== null ? (float) $product->price : null,
                formatted_price: $product->formatted_price ?? null,
                base_image_url: $product->getBaseImageUrlAttribute(),
            ))
            ->values()
            ->all();
    }
}
