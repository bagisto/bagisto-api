<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Webkul\Checkout\Contracts\Cart as CartContract;
use Webkul\Checkout\Facades\Cart;
use Webkul\Paypal\Http\Controllers\SmartButtonController;
use Webkul\Sales\Contracts\Order as OrderContract;

class PaypalSmartButtonCheckoutController extends SmartButtonController
{
    /**
     * The PayPal order status of a captured payment.
     */
    public const COMPLETED = 'COMPLETED';

    /**
     * Create the PayPal order for the cart from core's order body, with the cart id so the payment can be traced back to it.
     */
    public function createFor(CartContract $cart): array
    {
        $body = $this->requestBodyFor($cart);

        $body['purchase_units'][0]['custom_id'] = (string) $cart->id;

        return $this->toArray($this->smartButton->createOrder($body));
    }

    /**
     * Capture an approved PayPal order of the cart and place the order through core's flow, once; null when
     * the PayPal order was made for another cart or total, or PayPal did not complete the capture.
     */
    public function settle(string $paypalOrderId, CartContract $cart): ?OrderContract
    {
        return Cache::lock('bagistoapi.paypal-smart-button.order.'.$cart->id, 30)->block(10, function () use ($paypalOrderId, $cart) {
            if ($order = $this->orderRepository->findOneWhere(['cart_id' => $cart->id])) {
                return $order;
            }

            $paypalOrder = $this->toArray($this->smartButton->getOrder($paypalOrderId));

            if (! $this->paypalOrderMatchesCart($paypalOrder, $cart)) {
                return null;
            }

            if (
                ($paypalOrder['status'] ?? null) !== self::COMPLETED
                && ($this->toArray($this->smartButton->captureOrder($paypalOrderId))['status'] ?? null) !== self::COMPLETED
            ) {
                return null;
            }

            request()->merge(['orderData' => ['orderID' => $paypalOrderId]]);

            $this->saveOrder();

            return $this->orderRepository->findOneWhere(['cart_id' => $cart->id]);
        });
    }

    /**
     * Core's PayPal order body for the cart, which it builds from the current cart.
     */
    protected function requestBodyFor(CartContract $cart): array
    {
        Cart::setCart($cart);

        return $this->buildRequestBody();
    }

    /**
     * Whether the PayPal order was created for this cart, in its currency and for the total core asks PayPal to collect.
     */
    protected function paypalOrderMatchesCart(array $paypalOrder, CartContract $cart): bool
    {
        $purchaseUnit = $paypalOrder['purchase_units'][0] ?? [];

        $expected = $this->requestBodyFor($cart)['purchase_units'][0]['amount'];

        return (string) ($purchaseUnit['custom_id'] ?? '') === (string) $cart->id
            && ($purchaseUnit['amount']['currency_code'] ?? null) === $expected['currency_code']
            && abs((float) ($purchaseUnit['amount']['value'] ?? 0) - (float) $expected['value']) < 0.005;
    }

    /**
     * The PayPal SDK answers with model objects, which core also reads as arrays.
     */
    protected function toArray(mixed $result): array
    {
        return (array) json_decode(json_encode($result), true);
    }
}
