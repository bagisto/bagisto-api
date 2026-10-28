<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\ShippingRateOutput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\State\Concerns\FlattensShippingRates;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;

/**
 * Provides shipping rates for a cart.
 */
class ShippingRatesProvider implements ProviderInterface
{
    use FlattensShippingRates;

    public function __construct() {}

    /**
     * Provide shipping rates for the given cart token.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        if (! $cart->items->count() || ! $cart->shipping_address || ! $cart->haveStockableItems()) {
            return [];
        }

        Cart::setCart($cart);

        $result = Shipping::collectRates();

        if (! $result || ! is_array($result) || ! isset($result['shippingMethods'])) {
            return [];
        }

        $outputs = [];

        foreach ($this->flattenShippingRates($result['shippingMethods']) as $row) {
            $output = new ShippingRateOutput;

            foreach ($row as $property => $value) {
                $output->{$property} = $value;
            }

            $outputs[] = $output;
        }

        return $outputs;
    }
}
