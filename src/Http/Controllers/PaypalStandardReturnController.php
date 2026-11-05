<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Services\PaypalStandardReturnUrl;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

/**
 * Where PayPal Standard returns an API customer. Core's return page reads the cart from the browser
 * session, which a mobile WebView does not have, so the API places the order from the cart signed
 * into the URL, as core's StandardController::success does from the session cart, and then redirects
 * to the same storefront pages the other gateways' callbacks end on.
 */
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
     * Place the order for the cart once PayPal returns the customer.
     */
    public function success(int $cartId, string $signature): RedirectResponse
    {
        abort_unless(PaypalStandardReturnUrl::isValid($cartId, $signature), 404);

        Cache::lock('bagistoapi-paypal-standard-'.$cartId, 30)->block(10, fn () => $this->placeOrder($cartId));

        if ($order = $this->orderRepository->findOneWhere(['cart_id' => $cartId])) {
            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');
        }

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Send the customer back to the cart, as core's cancel does. No order is placed.
     */
    public function cancel(int $cartId, string $signature): RedirectResponse
    {
        abort_unless(PaypalStandardReturnUrl::isValid($cartId, $signature), 404);

        session()->flash('error', trans('shop::app.checkout.cart.paypal-payment-cancelled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Place the order unless the cart already has one or is no longer a PayPal Standard checkout.
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

            $order = $this->orderRepository->create((new OrderResource($cart))->jsonSerialize());

            Cart::deActivateCart();

            Event::dispatch('order.created.after', $order);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
