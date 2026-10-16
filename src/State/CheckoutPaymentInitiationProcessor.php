<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\CheckoutPaymentInitiationInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\CheckoutPaymentInitiation;
use Webkul\BagistoApi\State\Concerns\ResolvesCheckoutCart;
use Webkul\BagistoApi\State\Concerns\ValidatesCheckoutCart;
use Webkul\PayGlocal\Payment\PayGlocal;
use Webkul\Paypal\Payment\Standard;
use Webkul\PhonePe\Payment\PhonePe;
use Webkul\Razorpay\Payment\RazorpayPayment;
use Webkul\Stripe\Payment\Stripe;

/**
 * Starts payment for the cart's online gateway, as each gateway's storefront redirect action does.
 */
class CheckoutPaymentInitiationProcessor implements ProcessorInterface
{
    use ResolvesCheckoutCart;
    use ValidatesCheckoutCart;

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CheckoutPaymentInitiation
    {
        $cart = $this->resolveCheckoutCart();

        $this->assertCartHasNoErrors();

        $this->validateOrderCreation($cart);

        $method = (string) $cart->payment->method;

        $requested = $data instanceof CheckoutPaymentInitiationInput ? $data->payment_method : null;
        $requested ??= $context['args']['input']['paymentMethod'] ?? request()->input('payment_method') ?? request()->input('paymentMethod');

        if ($requested && $requested !== $method) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-method-mismatch'), 422);
        }

        try {
            $result = match ($method) {
                'stripe' => $this->stripe($cart),
                'phonepe' => $this->phonePe($cart),
                'payglocal' => $this->payGlocal($cart),
                'razorpay' => $this->razorpay($cart),
                'paypal_standard' => $this->paypalStandard(),
                'payu' => $this->payU($cart),
                default => throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-online'), 422),
            };
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-initiation-failed'), 422);
        }

        $result->method = $method;

        return $result;
    }

    private function stripe(object $cart): CheckoutPaymentInitiation
    {
        $stripe = app(Stripe::class);

        $this->assertCredentials($stripe->hasValidCredentials());

        return $this->redirect($stripe->createCheckoutSession($cart)->url);
    }

    private function phonePe(object $cart): CheckoutPaymentInitiation
    {
        $phonePe = app(PhonePe::class);

        $this->assertCredentials($phonePe->hasValidCredentials());
        $this->assertCurrency($phonePe->isCurrencySupported($this->baseCurrency($cart)), $phonePe->getSupportedCurrencies());

        return $this->redirect($phonePe->initiatePayment($cart));
    }

    private function payGlocal(object $cart): CheckoutPaymentInitiation
    {
        $payGlocal = app(PayGlocal::class);

        $this->assertCredentials($payGlocal->hasValidCredentials() && $payGlocal->hasUsableKeys());
        $this->assertCurrency($payGlocal->isCurrencySupported($payGlocal->getCurrency($cart)), $payGlocal->getAcceptedCurrencies());

        $response = $payGlocal->initiatePayment($cart, $payGlocal->generateMerchantTxnId($cart->id));

        return $this->redirect($response['redirectUrl'] ?? null);
    }

    private function razorpay(object $cart): CheckoutPaymentInitiation
    {
        $razorpay = app(RazorpayPayment::class);

        $this->assertCredentials($razorpay->hasValidCredentials());
        $this->assertCurrency($razorpay->isCurrencySupported($this->baseCurrency($cart)), $razorpay->getSupportedCurrencies());

        $result = new CheckoutPaymentInitiation;
        $result->type = 'sdk';
        $result->sdk_data = $razorpay->preparePaymentData($cart, $razorpay->createOrder($cart));

        return $result;
    }

    private function paypalStandard(): CheckoutPaymentInitiation
    {
        $paypal = app(Standard::class);

        return $this->form($paypal->getPaypalUrl(), $paypal->getFormFields());
    }

    private function payU(object $cart): CheckoutPaymentInitiation
    {
        $payU = app(config('payment_methods.payu.class'));

        return $this->form($payU->getPaymentUrl(), $payU->getPaymentData($cart));
    }

    private function redirect(?string $url): CheckoutPaymentInitiation
    {
        if (! $url) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-initiation-failed'), 422);
        }

        $result = new CheckoutPaymentInitiation;
        $result->type = 'redirect';
        $result->redirect_url = $url;

        return $result;
    }

    private function form(string $action, array $fields): CheckoutPaymentInitiation
    {
        $result = new CheckoutPaymentInitiation;
        $result->type = 'form';
        $result->form_action = $action;
        $result->form_fields = $fields;

        return $result;
    }

    private function assertCredentials(bool $valid): void
    {
        if (! $valid) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-configured'), 422);
        }
    }

    private function assertCurrency(bool $supported, array $supportedCurrencies): void
    {
        if (! $supported) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-currency-unsupported', [
                'currencies' => implode(', ', $supportedCurrencies),
            ]), 422);
        }
    }

    private function baseCurrency(object $cart): string
    {
        return strtoupper($cart->base_currency_code ?? core()->getBaseCurrencyCode());
    }
}
