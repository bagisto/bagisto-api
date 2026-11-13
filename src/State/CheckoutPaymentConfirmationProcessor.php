<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Http\Controllers\PaypalSmartButtonCheckoutController;
use Webkul\BagistoApi\Http\Controllers\RazorpayCheckoutController;
use Webkul\BagistoApi\Http\Controllers\StripeCheckoutController;
use Webkul\BagistoApi\Models\CheckoutPaymentConfirmation;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\State\Concerns\ResolvesCheckoutCart;
use Webkul\BagistoApi\State\Concerns\ValidatesCheckoutCart;
use Webkul\Checkout\Contracts\Cart as CartContract;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Contracts\Order as OrderContract;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Stripe\Payment\Stripe;

class CheckoutPaymentConfirmationProcessor implements ProcessorInterface
{
    use ResolvesCheckoutCart;
    use ValidatesCheckoutCart;

    /**
     * The payment methods whose gateway return places the order, so the confirmation only reads it.
     */
    public const RETURN_PLACED_METHODS = ['stripe', 'phonepe', 'payglocal', 'payu', 'paypal_standard'];

    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected StripeCheckoutController $stripeCheckout,
        protected Stripe $stripe,
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
    ) {}

    /**
     * Return the order of the bearer's payment, placing it after a verified Razorpay or PayPal Smart Button
     * payment, and never placing a second order for a cart that already has one.
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): CheckoutPaymentConfirmation {
        $request = Request::instance() ?? ($context['request'] ?? null);

        if ($sessionId = $this->input($data, $request, $context, 'sessionId')) {
            return $this->confirmStripeSession($sessionId, $request);
        }

        if ($reference = $this->input($data, $request, $context, 'reference')) {
            $cart = $this->resolveReferencedCart($reference);

            if ($order = $this->orderRepository->findOneWhere(['cart_id' => $cart->id])) {
                return $this->confirmed($order);
            }

            if (in_array($cart->payment?->method, self::RETURN_PLACED_METHODS, true)) {
                return $this->pending($cart);
            }
        }

        $cart = $this->resolveCheckoutCart();

        $method = $cart->payment?->method;

        if (in_array($method, self::RETURN_PLACED_METHODS, true)) {
            throw new InvalidInputException($method === 'stripe'
                ? trans('stripe::app.response.invalid-session')
                : __('bagistoapi::app.graphql.checkout.payment-reference-required'), 422);
        }

        if (! in_array($method, ['razorpay', 'paypal_smart_button'], true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-no-confirmation'), 422);
        }

        $this->assertCartHasNoErrors();

        $this->validateOrderCreation($cart);

        $order = $method === 'razorpay'
            ? $this->confirmRazorpay($cart, $data, $request, $context)
            : $this->confirmPaypalSmartButton($cart, $data, $request, $context);

        if (! $order) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-verified'), 422);
        }

        return $this->confirmed($order);
    }

    /**
     * Verify a paid Stripe checkout session against the bearer's cart and return its order, placing it
     * through core's settlement only when neither the webhook nor an earlier call has placed it yet.
     */
    protected function confirmStripeSession(string $sessionId, mixed $request): CheckoutPaymentConfirmation
    {
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

        return $this->confirmed($order, (int) $cart->id);
    }

    /**
     * Place the order after Razorpay's signature, order and payment are verified against Razorpay.
     */
    protected function confirmRazorpay(CartContract $cart, mixed $data, mixed $request, array $context): ?OrderContract
    {
        $fields = [
            'razorpay_order_id' => $this->input($data, $request, $context, 'razorpayOrderId'),
            'razorpay_payment_id' => $this->input($data, $request, $context, 'razorpayPaymentId'),
            'razorpay_signature' => $this->input($data, $request, $context, 'razorpaySignature'),
        ];

        if (in_array(null, $fields, true)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.razorpay-fields-required'), 422);
        }

        return app(RazorpayCheckoutController::class)->settle(new HttpRequest($fields), $cart);
    }

    /**
     * Capture the approved PayPal order and place the order, once the PayPal order is proven to be this cart's.
     */
    protected function confirmPaypalSmartButton(CartContract $cart, mixed $data, mixed $request, array $context): ?OrderContract
    {
        $paypalOrderId = $this->input($data, $request, $context, 'paypalOrderId');

        if (! $paypalOrderId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.paypal-order-id-required'), 422);
        }

        try {
            return app(PaypalSmartButtonCheckoutController::class)->settle($paypalOrderId, $cart);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * The confirmation of an order that exists; a PayPal Standard order stays pending until PayPal's IPN records the payment.
     */
    protected function confirmed(OrderContract $order, ?int $cartId = null): CheckoutPaymentConfirmation
    {
        $paid = $order->payment?->method !== 'paypal_standard'
            || $this->orderTransactionRepository->findOneWhere(['order_id' => $order->id]);

        $confirmation = new CheckoutPaymentConfirmation;
        $confirmation->success = (bool) $paid;
        $confirmation->cartId = $cartId ?? (int) $order->cart_id;
        $confirmation->orderId = (string) $order->id;
        $confirmation->orderIncrementId = (string) $order->increment_id;
        $confirmation->status = $order->status;
        $confirmation->paymentStatus = $paid ? 'success' : 'pending';
        $confirmation->message = $paid
            ? __('bagistoapi::app.graphql.checkout.payment-confirmed')
            : __('bagistoapi::app.graphql.checkout.payment-awaiting-confirmation');

        return $confirmation;
    }

    /**
     * The confirmation of a payment the gateway has not reported yet, so no order exists.
     */
    protected function pending(CartContract $cart): CheckoutPaymentConfirmation
    {
        $confirmation = new CheckoutPaymentConfirmation;
        $confirmation->cartId = (int) $cart->id;
        $confirmation->paymentStatus = 'pending';
        $confirmation->message = __('bagistoapi::app.graphql.checkout.payment-pending');

        return $confirmation;
    }

    /**
     * Read one input value from the GraphQL input or the REST body, which also takes the gateway's snake_case name.
     */
    protected function input(mixed $data, mixed $request, array $context, string $field): ?string
    {
        $value = $data instanceof CheckoutPaymentConfirmationInput ? $data->{$field} : null;

        $args = $context['args']['input'] ?? [];

        $value ??= is_array($args) ? ($args[$field] ?? null) : null;

        if (
            $value === null
            && $request
            && method_exists($request, 'input')
        ) {
            $value = $request->input($field) ?? $request->input(Str::snake($field));
        }

        return is_scalar($value) && trim((string) $value) !== '' ? (string) $value : null;
    }

    /**
     * Whether the cart, active or already converted, is the one the bearer's guest token or account holds.
     */
    protected function belongsToBearer(CartContract $cart, ?object $guestToken, ?object $customer): bool
    {
        if ($guestToken) {
            return (int) $guestToken->cart_id === (int) $cart->id;
        }

        return (int) $cart->customer_id === (int) $customer->id;
    }

    /**
     * Whether Stripe charged the cart's base currency and its base grand total, to the smallest unit.
     */
    protected function sessionMatchesCart(object $session, CartContract $cart): bool
    {
        if (strtolower((string) ($session->currency ?? '')) !== strtolower((string) $cart->base_currency_code)) {
            return false;
        }

        $decimal = core()->getBaseCurrency()->decimal ?? 2;

        $expected = (int) round((float) $cart->base_grand_total * (10 ** $decimal));

        return abs((int) ($session->amount_total ?? -1) - $expected) <= 1;
    }
}
