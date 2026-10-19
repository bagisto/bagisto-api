<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\ShippingRateOutput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;

class ShippingRatesProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct() {}

    /**
     * Provide shipping rates for the given cart token.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        if (
            ! $cart->items->count()
            || ! $cart->shipping_address
            || ! $cart->haveStockableItems()
        ) {
            return [];
        }

        Cart::setCart($cart);

        $result = Shipping::collectRates();

        if (
            ! $result
            || ! is_array($result)
            || ! isset($result['shippingMethods'])
        ) {
            return [];
        }

        $shippingMethods = $result['shippingMethods'];

        $outputs = [];
        foreach ($shippingMethods as $carrier => $group) {
            if (
                isset($group['rates'])
                && is_array($group['rates'])
            ) {
                foreach ($group['rates'] as $rate) {
                    $output = new ShippingRateOutput;

                    $output->id = (string) ($carrier.'_'.($rate->method ?? rand(1000, 9999)));
                    $output->code = (string) $carrier;
                    $output->label = (string) ($group['carrier_title'] ?? $carrier);
                    $output->method = (string) ($rate->method ?? $carrier);
                    $output->price = (float) ($rate->price ?? 0);
                    $output->formatted_price = (string) core()->formatPrice($rate->price ?? 0);
                    $output->description = (string) ($rate->method_description ?? '');
                    $output->method_title = (string) ($rate->method_title ?? $group['carrier_title'] ?? $carrier);
                    $output->method_description = (string) ($rate->method_description ?? '');
                    $output->base_price = (float) ($rate->base_price ?? 0);
                    $output->base_formatted_price = (string) ($rate->base_formatted_price ?? core()->currency($rate->base_price ?? 0));
                    $output->carrier = (string) $carrier;
                    $output->carrier_title = (string) ($group['carrier_title'] ?? $carrier);

                    $outputs[] = $output;
                }
            }
        }

        return $outputs;
    }
}
