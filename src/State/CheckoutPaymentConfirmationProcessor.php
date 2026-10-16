<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Http\Controllers\StripeCheckoutController;
use Webkul\BagistoApi\Models\CheckoutPaymentConfirmation;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\Checkout\Contracts\Cart as CartContract;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Stripe\Payment\Stripe;

class CheckoutPaymentConfirmationProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected StripeCheckoutController $stripeCheckout,
        protected Stripe $stripe,
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
    ) {}

    /**
     * Verify a paid Stripe checkout session against the bearer's cart and return its order, placing it
     * through core's settlement only when neither the webhook nor an earlier call has placed it yet.
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): CheckoutPaymentConfirmation {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $sessionId = $this->resolveSessionId($data, $request, $context);

        if (
            ! is_string($sessionId)
            || trim($sessionId) === ''
        ) {
            throw new InvalidInputException(trans('stripe::app.response.invalid-session'), 422);
        }

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $guestToken = $this->guestCartTokensRepository->findByToken($token);
        $customer = $guestToken ? null : CartTokenFacade::getCustomerByToken($token);

        if (
            ! $guestToken
            && ! $customer
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        $session = $this->stripe->retrieveCheckoutSession($sessionId);

        if (! $session) {
            throw new InvalidInputException(trans('stripe::app.response.session-invalid'), 422);
        }

        $cart = $this->cartRepository->find((int) ($session->metadata->cart_id ?? 0));

        if (
            ! $cart
            || ! $this->belongsToBearer($cart, $guestToken, $customer)
            || $cart->payment?->method !== 'stripe'
            || ! $this->sessionMatchesCart($session, $cart)
        ) {
            throw new InvalidInputException(trans('stripe::app.response.session-invalid'), 422);
        }

        if ($customer) {
            CartTokenFacade::actAsCustomer($customer);
        }

        $order = $this->orderRepository->findOneWhere(['cart_id' => $cart->id])
            ?? $this->stripeCheckout->settle($session);

        if (! $order) {
            throw new InvalidInputException($this->cartRepository->find($cart->id)?->is_active
                ? trans('stripe::app.response.cart-changed')
                : trans('stripe::app.response.cart-processed'), 422);
        }

        $confirmation = new CheckoutPaymentConfirmation;
        $confirmation->success = true;
        $confirmation->cartId = (int) $cart->id;
        $confirmation->orderId = (string) $order->id;
        $confirmation->message = __('bagistoapi::app.graphql.checkout.order-placed');

        return $confirmation;
    }

    /**
     * Read the Stripe checkout session id from the GraphQL input or the REST body.
     */
    private function resolveSessionId(mixed $data, mixed $request, array $context): ?string
    {
        if (
            $data instanceof CheckoutPaymentConfirmationInput
            && $data->sessionId !== null
        ) {
            return $data->sessionId;
        }

        $input = $context['args']['input'] ?? [];
        $sessionId = is_array($input) ? ($input['sessionId'] ?? null) : null;

        if (
            $request
            && method_exists($request, 'input')
        ) {
            $sessionId = $request->input('sessionId', $sessionId);
        }

        return is_string($sessionId) ? $sessionId : null;
    }

    /**
     * Whether the cart, active or already converted, is the one the bearer's guest token or account holds.
     */
    private function belongsToBearer(CartContract $cart, ?object $guestToken, ?object $customer): bool
    {
        if ($guestToken) {
            return (int) $guestToken->cart_id === (int) $cart->id;
        }

        return (int) $cart->customer_id === (int) $customer->id;
    }

    /**
     * Whether Stripe charged the cart's base currency and its base grand total, to the smallest unit.
     */
    private function sessionMatchesCart(object $session, CartContract $cart): bool
    {
        if (strtolower((string) ($session->currency ?? '')) !== strtolower((string) $cart->base_currency_code)) {
            return false;
        }

        $decimal = core()->getBaseCurrency()->decimal ?? 2;

        $expected = (int) round((float) $cart->base_grand_total * (10 ** $decimal));

        return abs((int) ($session->amount_total ?? -1) - $expected) <= 1;
    }
}
