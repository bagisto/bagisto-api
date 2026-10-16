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
}
