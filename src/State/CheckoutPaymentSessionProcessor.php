<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CheckoutPaymentSession;
use Webkul\Checkout\Contracts\Cart as CartContract;
use Webkul\Checkout\Facades\Cart;
use Webkul\Stripe\Payment\Stripe;

class CheckoutPaymentSessionProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(protected Stripe $stripe) {}

    /**
     * Create a Stripe Checkout Session for the bearer's cart, the headless counterpart of core's
     * Stripe redirect route, which reads the browser session's cart instead.
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): CheckoutPaymentSession {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        if (
            $cart->payment?->method !== 'stripe'
            || ! $this->stripe->isAvailable()
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.invalid-payment-method'), 422);
        }

        Cart::setCart($cart);

        Cart::collectTotals();

        $cart = Cart::getCart();

        $this->assertReadyForPayment($cart);

        try {
            $session = $this->stripe->createCheckoutSession($cart);
        } catch (\Throwable $exception) {
            report($exception);

            throw new OperationFailedException(trans('stripe::app.response.payment-failed'), 0, $exception);
        }

        $output = new CheckoutPaymentSession;
        $output->success = true;
        $output->cartId = (int) $cart->id;
        $output->sessionId = (string) $session->id;
        $output->redirectUrl = (string) $session->url;
        $output->message = __('bagistoapi::app.graphql.checkout.order-redirect-required');

        return $output;
    }

    /**
     * Stop unless the cart can become an order: it has items and no errors, a billing address and,
     * when it ships, a shipping address and a chosen rate.
     */
    private function assertReadyForPayment(?CartContract $cart): void
    {
        if (
            ! $cart
            || $cart->items()->count() === 0
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.cart-empty'), 422);
        }

        if (Cart::hasError()) {
            throw new InvalidInputException(Cart::getErrors()['message'] ?? __('bagistoapi::app.graphql.checkout.cart-empty'), 422);
        }

        if (! $cart->billing_address) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.billing-address-required'), 422);
        }

        if (! $cart->haveStockableItems()) {
            return;
        }

        if (! $cart->shipping_address) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.shipping-address-required'), 422);
        }

        if (! $cart->selected_shipping_rate) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.shipping-method-required'), 422);
        }
    }
}
