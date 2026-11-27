<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Webkul\BagistoApi\Services\PaypalStandardReturnUrl;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class PaypalStandardReturnController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
    ) {}

    /**
     * Place the cart's order pending once PayPal returns the customer, as core's return page does from the
     * session cart a WebView does not have, then send the customer to the storefront success page.
     */
    public function success(int $cartId, string $signature): RedirectResponse
    {
        abort_unless(PaypalStandardReturnUrl::isValid($cartId, $signature), 404);

        Cache::lock('bagistoapi.paypal-standard.order.'.$cartId, 30)->block(10, fn () => $this->placeOrder($cartId));

        if ($order = $this->orderRepository->findOneWhere(['cart_id' => $cartId])) {
            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');
        }

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Send the customer back to the cart without placing an order, as core's cancel does.
     */
    public function cancel(int $cartId, string $signature): RedirectResponse
    {
        abort_unless(PaypalStandardReturnUrl::isValid($cartId, $signature), 404);

        session()->flash('error', trans('shop::app.checkout.cart.paypal-payment-cancelled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Place the order unless the cart already has one or is no longer an active PayPal Standard checkout.
     */
    protected function placeOrder(int $cartId): void
    {
        if ($this->orderRepository->findOneWhere(['cart_id' => $cartId])) {
            return;
        }

        $cart = $this->cartRepository->find($cartId);

        if (
            ! $cart
            || ! $cart->is_active
            || $cart->payment?->method !== 'paypal_standard'
        ) {
            return;
        }

        try {
            Cart::setCart($cart);

            $this->orderRepository->create((new OrderResource($cart))->jsonSerialize());

            Cart::deActivateCart();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
