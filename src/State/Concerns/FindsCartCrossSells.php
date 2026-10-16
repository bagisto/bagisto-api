<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Collection;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Product;

/**
 * Cross-sell products for the cart of the Bearer token, mirroring the storefront
 * CartController::crossSellProducts: products already in the cart are left out.
 */
trait FindsCartCrossSells
{
    /**
     * @return Collection<int, Product>
     */
    protected function cartCrossSellProducts(): Collection
    {
        $token = TokenHeaderFacade::getAuthorizationBearerToken(request());

        $cart = $token ? CartTokenFacade::getCartByToken($token) : null;

        if (! $cart) {
            return collect();
        }

        $productIds = $cart->items->pluck('product_id')->all();

        return Product::query()
            ->select('products.*', 'product_cross_sells.child_id')
            ->join('product_cross_sells', 'products.id', '=', 'product_cross_sells.child_id')
            ->whereIn('product_cross_sells.parent_id', $productIds)
            ->whereNotIn('product_cross_sells.child_id', $productIds)
            ->groupBy('product_cross_sells.child_id')
            ->take(core()->getConfigData('catalog.products.cart_view_page.no_of_cross_sells_products'))
            ->get();
    }
}
