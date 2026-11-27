<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Webkul\Checkout\Contracts\Cart as CartContract;
use Webkul\Checkout\Facades\Cart;
use Webkul\Razorpay\Http\Controllers\RazorpayController;
use Webkul\Sales\Contracts\Order as OrderContract;

class RazorpayCheckoutController extends RazorpayController
{
    /**
     * Turn a verified Razorpay payment into the cart's order through core's settlement, once, and
     * return it; null when the signature, the Razorpay order or the payment does not match the cart.
     */
    public function settle(Request $request, CartContract $cart): ?OrderContract
    {
        return Cache::lock('bagistoapi.razorpay.order.'.$cart->id, 30)->block(10, function () use ($request, $cart) {
            if ($order = $this->orderRepository->findOneWhere(['cart_id' => $cart->id])) {
                return $order;
            }

            Cart::setCart($cart);

            if (
                ! $this->razorpayPayment->verifySignature(
                    $request->input('razorpay_order_id'),
                    $request->input('razorpay_payment_id'),
                    $request->input('razorpay_signature')
                )
                || ! $this->razorpayOrderBelongsToCart($request->input('razorpay_order_id'), $cart)
                || ! $this->paymentCoversCart($request, $cart)
            ) {
                return null;
            }

            $this->handlePaymentSuccess($request, $cart);

            return $this->orderRepository->findOneWhere(['cart_id' => $cart->id]);
        });
    }

    /**
     * Whether the Razorpay order was created for this cart; core trusts the browser session's cart instead.
     */
    protected function razorpayOrderBelongsToCart(?string $razorpayOrderId, CartContract $cart): bool
    {
        if (! $razorpayOrderId) {
            return false;
        }

        try {
            $razorpayOrder = $this->razorpayPayment->getApi()->order->fetch($razorpayOrderId);
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }

        return (string) ($razorpayOrder['notes']['cart_id'] ?? '') === (string) $cart->id;
    }
}
