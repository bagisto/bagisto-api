<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\CheckoutPaymentSessionInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Http\Controllers\PaypalSmartButtonCheckoutController;
use Webkul\BagistoApi\Models\CheckoutPaymentSession;
use Webkul\BagistoApi\Services\PaypalStandardReturnUrl;
use Webkul\BagistoApi\State\Concerns\ResolvesCheckoutCart;
use Webkul\BagistoApi\State\Concerns\ValidatesCheckoutCart;
use Webkul\Checkout\Contracts\Cart as CartContract;

class CheckoutPaymentSessionProcessor implements ProcessorInterface
{
    use ResolvesCheckoutCart;
    use ValidatesCheckoutCart;

    /**
     * The online payment methods and how each hands the customer to its gateway.
     */
    public const ONLINE_METHODS = [
        'stripe' => 'redirect',
        'phonepe' => 'redirect',
        'payglocal' => 'redirect',
        'payu' => 'form',
        'paypal_standard' => 'form',
        'razorpay' => 'sdk',
        'paypal_smart_button' => 'sdk',
    ];

    /**
     * Start payment for the bearer cart's online payment method, the headless counterpart of each gateway's
     * storefront redirect route, which reads the browser session's cart instead.
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): CheckoutPaymentSession {
        $cart = $this->resolveCheckoutCart();

        $this->assertCartHasNoErrors();

        $this->validateOrderCreation($cart);

        $method = (string) $cart->payment->method;

        $requested = $this->requestedMethod($data, $context);

        if (
            $requested
            && $requested !== $method
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-method-mismatch'), 422);
        }

        if (! isset(self::ONLINE_METHODS[$method])) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-online'), 422);
        }

        $session = new CheckoutPaymentSession;
        $session->method = $method;
        $session->type = self::ONLINE_METHODS[$method];

        try {
            match ($method) {
                'stripe' => $this->startStripe($session, $cart),
                'phonepe' => $this->startPhonePe($session, $cart),
                'payglocal' => $this->startPayGlocal($session, $cart),
                'payu' => $this->startPayU($session, $cart),
                'paypal_standard' => $this->startPaypalStandard($session, $cart),
                'razorpay' => $this->startRazorpay($session, $cart),
                'paypal_smart_button' => $this->startPaypalSmartButton($session, $cart),
            };
        } catch (InvalidInputException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-initiation-failed'), 422);
        }

        $session->success = true;
        $session->cartId = (int) $cart->id;
        $session->reference = (string) $cart->id;
        $session->message = __('bagistoapi::app.graphql.checkout.payment-started');

        if ($session->type !== 'sdk') {
            $session->successUrl = route('shop.checkout.onepage.success');
            $session->failureUrl = route('shop.checkout.cart.index');
        }

        return $session;
    }

    /**
     * Open a Stripe Checkout Session, whose return core's Stripe success route turns into the order.
     */
    protected function startStripe(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $stripe = $this->gateway('stripe');

        $this->assertConfigured($stripe->hasValidCredentials());

        $checkoutSession = $stripe->createCheckoutSession($cart);

        $session->sessionId = (string) $checkoutSession->id;
        $session->redirectUrl = $this->requireValue($checkoutSession->url);
    }

    /**
     * Start a PhonePe payment, whose return core's PhonePe callback turns into the order.
     */
    protected function startPhonePe(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $phonePe = $this->gateway('phonepe');

        $this->assertConfigured($phonePe->hasValidCredentials());

        $this->assertCurrency($phonePe->isCurrencySupported($this->baseCurrency($cart)), $phonePe->getSupportedCurrencies());

        $session->redirectUrl = $this->requireValue($phonePe->initiatePayment($cart));
    }

    /**
     * Start a PayGlocal payment, whose return core's PayGlocal callback turns into the order.
     */
    protected function startPayGlocal(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $payGlocal = $this->gateway('payglocal');

        $this->assertConfigured(
            $payGlocal->hasValidCredentials()
            && $payGlocal->hasUsableKeys()
        );

        $this->assertCurrency($payGlocal->isCurrencySupported($payGlocal->getCurrency($cart)), $payGlocal->getAcceptedCurrencies());

        $response = $payGlocal->initiatePayment($cart, $payGlocal->generateMerchantTxnId($cart->id));

        $session->redirectUrl = $this->requireValue($response['redirectUrl'] ?? null);
    }

    /**
     * Build the PayU form, whose return core's PayU success route turns into the order.
     */
    protected function startPayU(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $payU = $this->gateway('payu');

        $this->assertConfigured($payU->hasValidCredentials());

        $this->assertCurrency($payU->isCurrencySupported($this->baseCurrency($cart)), $payU->getSupportedCurrencies());

        $session->formAction = $payU->getPaymentUrl();
        $session->formFields = $payU->getPaymentData($cart);
    }

    /**
     * Build the PayPal Standard form, returning to the API's own pages because core's return page reads the session cart.
     */
    protected function startPaypalStandard(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $paypal = $this->gateway('paypal_standard');

        $this->assertConfigured((bool) $paypal->getConfigData('business_account'));

        $session->formAction = $paypal->getPaypalUrl();
        $session->formFields = array_merge($paypal->getFormFields(), [
            'return' => PaypalStandardReturnUrl::success((int) $cart->id),
            'cancel_return' => PaypalStandardReturnUrl::cancel((int) $cart->id),
        ]);
    }

    /**
     * Create the Razorpay order and return the options the Razorpay checkout opens with.
     */
    protected function startRazorpay(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $razorpay = $this->gateway('razorpay');

        $this->assertConfigured($razorpay->hasValidCredentials());

        $this->assertCurrency($razorpay->isCurrencySupported($this->baseCurrency($cart)), $razorpay->getSupportedCurrencies());

        $session->sdkData = $razorpay->preparePaymentData($cart, $razorpay->createOrder($cart));
    }

    /**
     * Create the PayPal order and return what the PayPal SDK needs to show the payment for approval.
     */
    protected function startPaypalSmartButton(CheckoutPaymentSession $session, CartContract $cart): void
    {
        $smartButton = $this->gateway('paypal_smart_button');

        $this->assertConfigured(
            (bool) $smartButton->getConfigData('client_id')
            && (bool) $smartButton->getConfigData('client_secret')
        );

        $acceptedCurrencies = array_values(array_filter(array_map('trim', explode(',', (string) $smartButton->getConfigData('accepted_currencies')))));

        $this->assertCurrency(
            ! $acceptedCurrencies
            || in_array($cart->cart_currency_code, $acceptedCurrencies, true),
            $acceptedCurrencies
        );

        $paypalOrder = app(PaypalSmartButtonCheckoutController::class)->createFor($cart);

        $session->sdkData = [
            'orderId' => $this->requireValue($paypalOrder['id'] ?? null),
            'clientId' => $smartButton->getConfigData('client_id'),
            'currency' => $cart->cart_currency_code,
            'intent' => 'capture',
        ];
    }

    /**
     * The payment class core registers for the method.
     */
    protected function gateway(string $method): object
    {
        return app(config("payment_methods.{$method}.class"));
    }

    /**
     * The payment method the client sent, from the GraphQL input or the REST body.
     */
    protected function requestedMethod(mixed $data, array $context): ?string
    {
        $method = $data instanceof CheckoutPaymentSessionInput ? $data->paymentMethod : null;

        $method ??= $context['args']['input']['paymentMethod'] ?? request()->input('paymentMethod') ?? request()->input('payment_method');

        return is_string($method) && $method !== '' ? $method : null;
    }

    /**
     * Stop when the merchant has not entered the gateway's credentials.
     */
    protected function assertConfigured(bool $configured): void
    {
        if (! $configured) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-configured'), 422);
        }
    }

    /**
     * Stop when the gateway does not take the store currency, before the customer is sent to pay.
     */
    protected function assertCurrency(bool $supported, array $supportedCurrencies): void
    {
        if (! $supported) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-currency-unsupported', [
                'currencies' => implode(', ', $supportedCurrencies),
            ]), 422);
        }
    }

    /**
     * Stop when the gateway answered without what the client needs to continue.
     */
    protected function requireValue(mixed $value): string
    {
        if (
            ! is_string($value)
            || $value === ''
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-initiation-failed'), 422);
        }

        return $value;
    }

    /**
     * The cart's base currency, which the gateways charge in.
     */
    protected function baseCurrency(CartContract $cart): string
    {
        return strtoupper($cart->base_currency_code ?? core()->getBaseCurrencyCode());
    }
}
