<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Cart as CartModel;
use Webkul\Checkout\Facades\Cart;

class CartProvider implements ProviderInterface
{
    /**
     * Provide cart data for authenticated or guest users.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        Cart::collectTotals();
        $cartModel = Cart::getCart();

        if (! $cartModel) {
            return null;
        }

        return $this->buildCartResponse($cartModel);
    }

    /**
     * Build cart response.
     */
    private function buildCartResponse($cart): CartModel
    {
        $cartResponse = new CartModel;
        $cartResponse->id = (string) $cart->id;
        $cartResponse->customer_id = $cart->customer_id;
        $cartResponse->quote_id = $cart->quote_id;

        $items = [];
        foreach ($cart->items as $item) {
            $items[] = [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'quantity'     => $item->quantity,
                'sku'          => $item->product?->sku,
                'name'         => $item->product?->name,
                'price'        => $item->price,
                'base_price'   => $item->base_price,
                'total'        => $item->total,
                'base_total'   => $item->base_total,
                'options'      => $item->options ? json_decode($item->options, true) : [],
            ];
        }

        $cartResponse->items = $items;
        $cartResponse->subtotal = (float) ($cart->sub_total ?? 0);
        $cartResponse->base_subtotal = (float) ($cart->base_sub_total ?? 0);
        $cartResponse->tax = (float) ($cart->tax_total ?? 0);
        $cartResponse->base_tax = (float) ($cart->base_tax_total ?? 0);
        $cartResponse->discount = (float) ($cart->discount_amount ?? 0);
        $cartResponse->base_discount = (float) ($cart->base_discount_amount ?? 0);
        $cartResponse->total = (float) ($cart->grand_total ?? 0);
        $cartResponse->base_total = (float) ($cart->base_grand_total ?? 0);
        $cartResponse->items_count = (int) ($cart->items_count ?? 0);
        $cartResponse->coupon_code = $cart->coupon_code;
        $cartResponse->created_at = $cart->created_at?->toIso8601String();
        $cartResponse->updated_at = $cart->updated_at?->toIso8601String();

        return $cartResponse;
    }
}
