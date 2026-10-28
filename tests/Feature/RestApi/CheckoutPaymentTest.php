<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\Concerns\PreparesCheckoutPayment;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class CheckoutPaymentTest extends RestApiTestCase
{
    use PreparesCheckoutPayment;

    public function test_initiate_requires_a_cart_token(): void
    {
        $this->seedRequiredData();

        expect($this->publicPost('/api/shop/checkout-payments/initiate')->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_initiate_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertStatus(422);
    }

    public function test_initiate_refuses_a_payment_method_that_does_not_match_the_cart(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $this->readyCart('paypal_standard');

        $this->checkoutPost('/api/shop/checkout-payments/initiate', ['paymentMethod' => 'stripe'])->assertStatus(422);
    }

    public function test_initiate_paypal_standard_returns_the_paypal_form(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();
        expect($response->json('type'))->toBe('form');
        expect($response->json('formAction'))->toContain('paypal.com/cgi-bin/webscr');
        expect((int) $response->json('formFields.invoice'))->toBe($this->activeCartId($customer));
        expect($response->json('formFields.business'))->toBe('merchant@example.com');
    }

    public function test_confirm_paypal_standard_places_a_pending_order(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm');

        $response->assertCreated();
        expect($response->json('status'))->toBe('pending');
        $this->assertDatabaseHas('orders', ['id' => $response->json('orderId'), 'cart_id' => $cartId]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);
    }

    public function test_confirm_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $this->checkoutPost('/api/shop/checkout-payments/confirm')->assertStatus(422);
    }

    public function test_initiate_razorpay_returns_the_sdk_data(): void
    {
        $this->enableRazorpay();
        $customer = $this->readyCart('razorpay');
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => 0], 'amount' => 0]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();
        expect($response->json('type'))->toBe('sdk');
        expect($response->json('sdkData.order_id'))->toBe('order_test_1');
        expect($response->json('sdkData.key'))->toBe('rzp_test_key');
    }

    public function test_confirm_razorpay_places_a_paid_order(): void
    {
        $this->enableRazorpay();
        $customer = $this->readyCart('razorpay');
        $cartId = $this->activeCartId($customer);
        $amount = (int) (DB::table('cart')->where('id', $cartId)->value('base_grand_total') * 100);
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId], 'amount' => $amount]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpay_order_id' => 'order_test_1',
            'razorpay_payment_id' => 'pay_test_1',
            'razorpay_signature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ]);

        $response->assertCreated();
        expect($response->json('status'))->toBe('processing');
        $this->assertDatabaseHas('order_transactions', ['order_id' => $response->json('orderId'), 'transaction_id' => 'pay_test_1']);
        $this->assertDatabaseHas('invoices', ['order_id' => $response->json('orderId')]);

        $paid = DB::table('order_transactions')->where('transaction_id', 'pay_test_1')->value('amount');
        expect((float) $paid)->toBe((float) DB::table('orders')->where('id', $response->json('orderId'))->value('base_grand_total'));
    }

    public function test_confirm_razorpay_refuses_a_wrong_signature(): void
    {
        $this->enableRazorpay();
        $customer = $this->readyCart('razorpay');
        $cartId = $this->activeCartId($customer);
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId], 'amount' => 0]);

        $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpay_order_id' => 'order_test_1',
            'razorpay_payment_id' => 'pay_test_1',
            'razorpay_signature' => 'forged',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    public function test_confirm_razorpay_refuses_a_payment_made_for_another_cart(): void
    {
        $this->enableRazorpay();
        $customer = $this->readyCart('razorpay');
        $cartId = $this->activeCartId($customer);
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId + 1000], 'amount' => 100]);

        $this->checkoutPost('/api/shop/checkout-payments/confirm', [
            'razorpay_order_id' => 'order_test_1',
            'razorpay_payment_id' => 'pay_test_1',
            'razorpay_signature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ])->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    /**
     * A cart paying by PayPal Smart Button, with the method configured for the cart currency.
     */
    private function readySmartButtonCart(): int
    {
        $customer = $this->readyCart('paypal_smart_button');
        $cartId = $this->activeCartId($customer);

        $this->enableSmartButton((string) DB::table('cart')->where('id', $cartId)->value('cart_currency_code'));

        return $cartId;
    }

    public function test_initiate_smart_button_returns_the_paypal_order(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();
        expect($response->json('method'))->toBe('paypal_smart_button');
        expect($response->json('type'))->toBe('sdk');
        expect($response->json('sdkData.order_id'))->toBe('PAYPAL-ORDER-1');
        expect($response->json('sdkData.client_id'))->toBe('paypal_test_client');
        expect($response->json('sdkData.currency'))->toBe(DB::table('cart')->where('id', $cartId)->value('cart_currency_code'));
    }

    public function test_initiate_smart_button_refuses_when_paypal_is_not_configured(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->enablePaymentMethod('paypal_smart_button', ['client_secret' => '']);
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertStatus(422);

        $smartButton->shouldNotHaveReceived('createOrder');
    }

    public function test_initiate_smart_button_refuses_an_unaccepted_currency(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->enableSmartButton('XYZ');
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/initiate')->assertStatus(422);
    }

    public function test_confirm_smart_button_captures_and_places_a_paid_order(): void
    {
        $cartId = $this->readySmartButtonCart();
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypal_order_id' => 'PAYPAL-ORDER-1']);

        $response->assertCreated();
        expect($response->json('status'))->toBe('processing');
        expect($response->json('success'))->toBeTrue();
        $smartButton->shouldHaveReceived('captureOrder')->once();
        $this->assertDatabaseHas('orders', ['id' => $response->json('orderId'), 'cart_id' => $cartId]);
        $this->assertDatabaseHas('invoices', ['order_id' => $response->json('orderId')]);
        $this->assertDatabaseHas('order_transactions', ['order_id' => $response->json('orderId'), 'transaction_id' => 'PAYPAL-ORDER-1', 'payment_method' => 'paypal_smart_button']);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);
    }

    public function test_confirm_smart_button_requires_the_paypal_order_id(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $this->checkoutPost('/api/shop/checkout-payments/confirm')->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    public function test_confirm_smart_button_refuses_a_paypal_order_of_another_cart(): void
    {
        $cartId = $this->readySmartButtonCart();
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId + 1000));

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypal_order_id' => 'PAYPAL-ORDER-1'])->assertStatus(422);

        $smartButton->shouldNotHaveReceived('captureOrder');
        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    public function test_confirm_smart_button_refuses_when_the_capture_does_not_complete(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId), 'DECLINED');

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['paypal_order_id' => 'PAYPAL-ORDER-1'])->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }
}
