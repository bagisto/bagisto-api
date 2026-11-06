<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Services\PaypalStandardReturnUrl;
use Webkul\BagistoApi\Tests\Concerns\PreparesCheckoutPayment;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\PayGlocal\Payment\PayGlocal;
use Webkul\PhonePe\Payment\PhonePe;
use Webkul\Stripe\Payment\Stripe;

class CheckoutPaymentTest extends RestApiTestCase
{
    use PreparesCheckoutPayment;

    // ============================================================================
    // Initiate
    // ============================================================================

    /**
     * Test initiate requires a cart token.
     */
    public function test_initiate_requires_a_cart_token(): void
    {
        $this->seedRequiredData();

        expect($this->publicPost('/api/shop/checkout-payments/initiate')->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Test initiate refuses an offline payment method.
     */
    public function test_initiate_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertUnprocessable();
    }

    /**
     * Test initiate refuses a payment method that does not match the cart.
     */
    public function test_initiate_refuses_a_payment_method_that_does_not_match_the_cart(): void
    {
        $this->enablePaypalStandard();
        $this->readyCart('paypal_standard');

        $this->checkoutPost('/api/shop/checkout-payments/initiate', ['paymentMethod' => 'stripe'])->assertUnprocessable();
    }

    /**
     * Test initiate stripe opens a checkout session.
     */
    public function test_initiate_stripe_opens_a_checkout_session(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));
        $stripe = $this->fakeGateway(Stripe::class, [
            'createCheckoutSession' => (object) ['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1'],
        ]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('method'))->toBe('stripe')
            ->and($response->json('type'))->toBe('redirect')
            ->and($response->json('reference'))->toBe((string) $cartId)
            ->and($response->json('sessionId'))->toBe('cs_test_1')
            ->and($response->json('redirectUrl'))->toBe('https://checkout.stripe.com/c/pay/cs_test_1')
            ->and($response->json('successUrl'))->toBe(route('shop.checkout.onepage.success'))
            ->and($response->json('failureUrl'))->toBe(route('shop.checkout.cart.index'));

        $stripe->shouldHaveReceived('createCheckoutSession')->once();
    }

    /**
     * Test initiate phonepe returns the phonepe payment page.
     */
    public function test_initiate_phonepe_returns_the_phonepe_payment_page(): void
    {
        $this->enablePaymentMethod('phonepe', ['merchant_id' => 'M1', 'client_id' => 'phonepe_client', 'client_secret' => 'phonepe_secret']);
        $cartId = $this->activeCartId($this->readyCart('phonepe'));
        $this->fakeGateway(PhonePe::class, [
            'isCurrencySupported' => true,
            'initiatePayment' => 'https://mercury.phonepe.com/transact/pg?token=test',
        ]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('type'))->toBe('redirect')
            ->and($response->json('reference'))->toBe((string) $cartId)
            ->and($response->json('redirectUrl'))->toBe('https://mercury.phonepe.com/transact/pg?token=test');
    }

    /**
     * Test initiate phonepe refuses when phonepe answers without a payment page.
     */
    public function test_initiate_phonepe_refuses_when_phonepe_answers_without_a_payment_page(): void
    {
        $this->enablePaymentMethod('phonepe', ['merchant_id' => 'M1', 'client_id' => 'phonepe_client', 'client_secret' => 'phonepe_secret']);
        $this->readyCart('phonepe');
        $this->fakeGateway(PhonePe::class, [
            'isCurrencySupported' => true,
            'initiatePayment' => null,
        ]);

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertUnprocessable();
    }

    /**
     * Test initiate payglocal returns the payglocal payment page.
     */
    public function test_initiate_payglocal_returns_the_payglocal_payment_page(): void
    {
        $this->enablePaymentMethod('payglocal', ['merchant_id' => 'M1', 'public_key_id' => 'pub', 'private_key_id' => 'priv', 'payglocal_public_key' => 'key', 'merchant_private_key' => 'key']);
        $cartId = $this->activeCartId($this->readyCart('payglocal'));
        $payGlocal = $this->fakeGateway(PayGlocal::class, [
            'hasUsableKeys' => true,
            'isCurrencySupported' => true,
            'initiatePayment' => ['redirectUrl' => 'https://payglocal.in/pay/test'],
        ]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('type'))->toBe('redirect')
            ->and($response->json('reference'))->toBe((string) $cartId)
            ->and($response->json('redirectUrl'))->toBe('https://payglocal.in/pay/test');

        $payGlocal->shouldHaveReceived('initiatePayment')->once();
    }

    /**
     * Test initiate payglocal refuses unusable keys.
     */
    public function test_initiate_payglocal_refuses_unusable_keys(): void
    {
        $this->enablePaymentMethod('payglocal', ['merchant_id' => 'M1', 'public_key_id' => 'pub', 'private_key_id' => 'priv', 'payglocal_public_key' => 'key', 'merchant_private_key' => 'key']);
        $this->readyCart('payglocal');
        $payGlocal = $this->fakeGateway(PayGlocal::class, [
            'hasUsableKeys' => false,
            'isCurrencySupported' => true,
        ]);

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertUnprocessable();

        $payGlocal->shouldNotHaveReceived('initiatePayment');
    }

    /**
     * Test initiate paypal standard returns the paypal form returning to the api.
     */
    public function test_initiate_paypal_standard_returns_the_paypal_form_returning_to_the_api(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('method'))->toBe('paypal_standard')
            ->and($response->json('type'))->toBe('form')
            ->and($response->json('reference'))->toBe((string) $cartId)
            ->and($response->json('formAction'))->toContain('paypal.com/cgi-bin/webscr')
            ->and((int) $response->json('formFields.invoice'))->toBe($cartId)
            ->and($response->json('formFields.business'))->toBe('merchant@example.com')
            ->and($response->json('formFields.return'))->toBe(PaypalStandardReturnUrl::success($cartId))
            ->and($response->json('formFields.cancel_return'))->toBe(PaypalStandardReturnUrl::cancel($cartId))
            ->and($response->json('formFields.notify_url'))->toBe(route('paypal.standard.ipn'))
            ->and($response->json('successUrl'))->toBe(route('shop.checkout.onepage.success'))
            ->and($response->json('failureUrl'))->toBe(route('shop.checkout.cart.index'));
    }

    /**
     * Test initiate payu returns the payu form.
     */
    public function test_initiate_payu_returns_the_payu_form(): void
    {
        $this->enablePayU();
        $cartId = $this->activeCartId($this->readyCart('payu'));
        $this->useCartCurrency('INR');

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('type'))->toBe('form')
            ->and($response->json('reference'))->toBe((string) $cartId)
            ->and($response->json('formFields.key'))->toBe('payu_test_key')
            ->and($response->json('formFields.hash'))->not->toBeEmpty();
    }

    /**
     * Test initiate refuses a currency the gateway does not take.
     */
    public function test_initiate_refuses_a_currency_the_gateway_does_not_take(): void
    {
        $this->enableRazorpay();
        $this->readyCart('razorpay');
        $this->useCartCurrency('USD');

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertUnprocessable();

        expect($response->json('detail') ?? $response->json('message'))->toContain('INR');
    }

    /**
     * Test initiate razorpay returns the sdk data.
     */
    public function test_initiate_razorpay_returns_the_sdk_data(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));
        $this->useCartCurrency('INR');
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId]]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('type'))->toBe('sdk')
            ->and($response->json('sdkData.order_id'))->toBe('order_test_1')
            ->and($response->json('sdkData.key'))->toBe('rzp_test_key')
            ->and($response->json('successUrl'))->toBeNull();
    }

    /**
     * Test initiate smart button returns the paypal order.
     */
    public function test_initiate_smart_button_returns_the_paypal_order(): void
    {
        $cartId = $this->readySmartButtonCart();
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();

        expect($response->json('method'))->toBe('paypal_smart_button')
            ->and($response->json('type'))->toBe('sdk')
            ->and($response->json('sdkData.orderId'))->toBe('PAYPAL-ORDER-1')
            ->and($response->json('sdkData.clientId'))->toBe('paypal_test_client');

        $smartButton->shouldHaveReceived('createOrder')->with(\Mockery::on(
            fn (array $body) => $body['purchase_units'][0]['custom_id'] === (string) $cartId
        ));
    }

    /**
     * Test initiate smart button refuses when paypal is not configured.
     */
    public function test_initiate_smart_button_refuses_when_paypal_is_not_configured(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->enablePaymentMethod('paypal_smart_button', ['client_secret' => '']);
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertUnprocessable();

        $smartButton->shouldNotHaveReceived('createOrder');
    }

    /**
     * Test initiate smart button refuses an unaccepted currency.
     */
    public function test_initiate_smart_button_refuses_an_unaccepted_currency(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->enableSmartButton('XYZ');
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertUnprocessable();
    }

    /**
     * Test the smart button order cannot be placed without payment.
     */
    public function test_the_smart_button_order_cannot_be_placed_without_payment(): void
    {
        $cartId = $this->readySmartButtonCart();

        $this->checkoutPost('/api/shop/checkout-orders')->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    // ============================================================================
    // Confirm After A Gateway Return
    // ============================================================================

    /**
     * Test confirm returns the order a gateway callback placed.
     */
    public function test_confirm_returns_the_order_a_gateway_callback_placed(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));

        $order = $this->placeOrderAsGatewayCallback($cartId);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();

        expect($response->json('paymentStatus'))->toBe('success')
            ->and($response->json('success'))->toBeTrue()
            ->and($response->json('cartId'))->toBe($cartId)
            ->and($response->json('orderId'))->toBe((string) $order->id)
            ->and($response->json('orderIncrementId'))->toBe((string) $order->increment_id);
    }

    /**
     * Test confirm reports pending until the gateway callback places the order.
     */
    public function test_confirm_reports_pending_until_the_gateway_callback_places_the_order(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();

        expect($response->json('paymentStatus'))->toBe('pending')
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('orderId'))->toBeNull();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 1]);
    }

    /**
     * Test confirm with the reference is safe to repeat.
     */
    public function test_confirm_with_the_reference_is_safe_to_repeat(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));
        $this->placeOrderAsGatewayCallback($cartId);

        $first = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertCreated();
        $again = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertCreated();

        expect($again->json('orderId'))->toBe($first->json('orderId'))
            ->and(DB::table('orders')->where('cart_id', $cartId)->count())->toBe(1);
    }

    /**
     * Test confirm refuses a reference of another customer.
     */
    public function test_confirm_refuses_a_reference_of_another_customer(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));
        $this->placeOrderAsGatewayCallback($cartId);

        $this->readyCart('stripe');

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertNotFound();
    }

    /**
     * Test confirm without a reference is refused for a gateway that places the order on its return.
     */
    public function test_confirm_without_a_reference_is_refused_for_a_return_placed_gateway(): void
    {
        $this->enablePayU();
        $this->readyCart('payu');

        $this->checkoutPost('/api/shop/checkout-payments/confirm')->assertUnprocessable();
    }

    /**
     * Test confirm refuses an offline payment method.
     */
    public function test_confirm_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $this->checkoutPost('/api/shop/checkout-payments/confirm')->assertUnprocessable();
    }

    // ============================================================================
    // PayPal Standard Return
    // ============================================================================

    /**
     * Test the paypal standard return places the order pending and confirm reports it pending.
     */
    public function test_the_paypal_standard_return_places_the_order_pending(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $this->get(PaypalStandardReturnUrl::success($cartId).'?tx=TX1&st=Completed')
            ->assertRedirect(route('shop.checkout.onepage.success'));

        $this->assertDatabaseHas('orders', ['cart_id' => $cartId, 'status' => 'pending']);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();

        expect($response->json('paymentStatus'))->toBe('pending')
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('orderId'))->not->toBeNull();
    }

    /**
     * Test the paypal standard confirm reports success once paypal recorded the payment.
     */
    public function test_the_paypal_standard_confirm_reports_success_once_paypal_recorded_the_payment(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $this->get(PaypalStandardReturnUrl::success($cartId))->assertRedirect(route('shop.checkout.onepage.success'));

        $orderId = (int) DB::table('orders')->where('cart_id', $cartId)->value('id');

        DB::table('order_transactions')->insert([
            'transaction_id' => 'TX1',
            'status' => 'Completed',
            'type' => 'instant',
            'payment_method' => 'paypal_standard',
            'order_id' => $orderId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();

        expect($response->json('paymentStatus'))->toBe('success')
            ->and($response->json('success'))->toBeTrue()
            ->and($response->json('orderId'))->toBe((string) $orderId);
    }

    /**
     * Test the paypal standard return is safe to repeat.
     */
    public function test_the_paypal_standard_return_is_safe_to_repeat(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $this->get(PaypalStandardReturnUrl::success($cartId))->assertRedirect(route('shop.checkout.onepage.success'));
        $this->get(PaypalStandardReturnUrl::success($cartId))->assertRedirect(route('shop.checkout.onepage.success'));

        expect(DB::table('orders')->where('cart_id', $cartId)->count())->toBe(1);
    }

    /**
     * Test the paypal standard cancel places no order.
     */
    public function test_the_paypal_standard_cancel_places_no_order(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $this->get(PaypalStandardReturnUrl::cancel($cartId))->assertRedirect(route('shop.checkout.cart.index'));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        expect($response->json('paymentStatus'))->toBe('pending')
            ->and($response->json('orderId'))->toBeNull();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 1]);
    }

    /**
     * Test the paypal standard return refuses a forged signature.
     */
    public function test_the_paypal_standard_return_refuses_a_forged_signature(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $this->get('/api/checkout/paypal-standard/'.$cartId.'/forged/success')->assertNotFound();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    // ============================================================================
    // Razorpay
    // ============================================================================

    /**
     * Test confirm razorpay places a paid order through core's settlement.
     */
    public function test_confirm_razorpay_places_a_paid_order(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));
        $this->useCartCurrency('INR');
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId]], $this->razorpayPayment($cartId, 'order_test_1'));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpay_order_id' => 'order_test_1',
            'razorpay_payment_id' => 'pay_test_1',
            'razorpay_signature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ]);

        $response->assertCreated();

        expect($response->json('status'))->toBe('processing')
            ->and($response->json('paymentStatus'))->toBe('success');

        $this->assertDatabaseHas('order_transactions', ['order_id' => $response->json('orderId'), 'transaction_id' => 'pay_test_1']);
        $this->assertDatabaseHas('invoices', ['order_id' => $response->json('orderId')]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);
    }

    /**
     * Test confirm razorpay refuses a wrong signature.
     */
    public function test_confirm_razorpay_refuses_a_wrong_signature(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));
        $this->useCartCurrency('INR');
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId]], $this->razorpayPayment($cartId, 'order_test_1'));

        $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpayOrderId' => 'order_test_1',
            'razorpayPaymentId' => 'pay_test_1',
            'razorpaySignature' => 'forged',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test confirm razorpay refuses a razorpay order made for another cart.
     */
    public function test_confirm_razorpay_refuses_a_razorpay_order_of_another_cart(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));
        $this->useCartCurrency('INR');
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId + 1000]], $this->razorpayPayment($cartId, 'order_test_1'));

        $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpayOrderId' => 'order_test_1',
            'razorpayPaymentId' => 'pay_test_1',
            'razorpaySignature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test confirm razorpay refuses a payment that does not cover the cart.
     */
    public function test_confirm_razorpay_refuses_a_payment_that_does_not_cover_the_cart(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));
        $this->useCartCurrency('INR');
        $this->fakeRazorpayApi(
            ['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId]],
            ['amount' => 100] + $this->razorpayPayment($cartId, 'order_test_1')
        );

        $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpayOrderId' => 'order_test_1',
            'razorpayPaymentId' => 'pay_test_1',
            'razorpaySignature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test confirm razorpay requires the razorpay values.
     */
    public function test_confirm_razorpay_requires_the_razorpay_values(): void
    {
        $this->enableRazorpay();
        $this->readyCart('razorpay');
        $this->useCartCurrency('INR');

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['razorpayOrderId' => 'order_test_1'])->assertUnprocessable();
    }

    // ============================================================================
    // PayPal Smart Button
    // ============================================================================

    /**
     * Test confirm smart button captures and places a paid order.
     */
    public function test_confirm_smart_button_captures_and_places_a_paid_order(): void
    {
        $cartId = $this->readySmartButtonCart();
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypalOrderId' => 'PAYPAL-ORDER-1']);

        $response->assertCreated();

        expect($response->json('status'))->toBe('processing')
            ->and($response->json('success'))->toBeTrue();

        $smartButton->shouldHaveReceived('captureOrder')->once();

        $this->assertDatabaseHas('orders', ['id' => $response->json('orderId'), 'cart_id' => $cartId]);
        $this->assertDatabaseHas('invoices', ['order_id' => $response->json('orderId')]);
        $this->assertDatabaseHas('order_transactions', ['order_id' => $response->json('orderId'), 'transaction_id' => 'PAYPAL-ORDER-1', 'payment_method' => 'paypal_smart_button']);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);
    }

    /**
     * Test confirm smart button requires the paypal order id.
     */
    public function test_confirm_smart_button_requires_the_paypal_order_id(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/confirm')->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test confirm smart button refuses a paypal order of another cart.
     */
    public function test_confirm_smart_button_refuses_a_paypal_order_of_another_cart(): void
    {
        $cartId = $this->readySmartButtonCart();
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId, customId: $cartId + 1000));

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypalOrderId' => 'PAYPAL-ORDER-1'])->assertUnprocessable();

        $smartButton->shouldNotHaveReceived('captureOrder');

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test confirm smart button refuses when the capture does not complete.
     */
    public function test_confirm_smart_button_refuses_when_the_capture_does_not_complete(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId), 'DECLINED');

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypalOrderId' => 'PAYPAL-ORDER-1'])->assertUnprocessable();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * Charge the active carts in the given base currency, as a store with that base currency does.
     */
    private function useCartCurrency(string $code): void
    {
        DB::table('cart')->where('is_active', 1)->update([
            'global_currency_code' => $code,
            'base_currency_code' => $code,
        ]);
    }
}
