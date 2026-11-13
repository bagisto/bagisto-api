<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Services\PaypalStandardReturnUrl;
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

    public function test_initiate_paypal_standard_returns_the_api_return_pages(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();
        expect($response->json('successUrl'))->toBe(route('shop.checkout.onepage.success'));
        expect($response->json('failureUrl'))->toBe(route('shop.checkout.cart.index'));
        expect($response->json('formFields.return'))->toBe(PaypalStandardReturnUrl::success($cartId));
        expect($response->json('formFields.cancel_return'))->toBe(PaypalStandardReturnUrl::cancel($cartId));
        expect($response->json('formFields.notify_url'))->toBe(route('paypal.standard.ipn'));
    }

    public function test_paypal_standard_return_places_the_order_and_confirm_reports_it_pending(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $this->get(PaypalStandardReturnUrl::success($cartId).'?tx=TX1&st=Completed')->assertRedirect(route('shop.checkout.onepage.success'));

        $this->assertDatabaseHas('orders', ['cart_id' => $cartId, 'status' => 'pending']);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 0]);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();
        expect($response->json('paymentStatus'))->toBe('pending');
        expect($response->json('success'))->toBeFalse();
        expect($response->json('orderId'))->not->toBeNull();
    }

    public function test_paypal_standard_confirm_reports_success_once_paypal_recorded_the_payment(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

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
        expect($response->json('paymentStatus'))->toBe('success');
        expect($response->json('success'))->toBeTrue();
        expect($response->json('orderId'))->toBe($orderId);
    }

    public function test_paypal_standard_return_is_safe_to_repeat(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $this->get(PaypalStandardReturnUrl::success($cartId))->assertRedirect(route('shop.checkout.onepage.success'));
        $this->get(PaypalStandardReturnUrl::success($cartId))->assertRedirect(route('shop.checkout.onepage.success'));

        expect(DB::table('orders')->where('cart_id', $cartId)->count())->toBe(1);
    }

    public function test_paypal_standard_cancel_places_no_order(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $this->get(PaypalStandardReturnUrl::cancel($cartId))->assertRedirect(route('shop.checkout.cart.index'));

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        expect($response->json('paymentStatus'))->toBe('pending');
        expect($response->json('orderId'))->toBeNull();
        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 1]);
    }

    public function test_paypal_standard_confirm_without_return_places_no_order(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm');

        expect($response->json('paymentStatus'))->toBe('pending');
        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    public function test_paypal_standard_return_refuses_a_forged_signature(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $customer = $this->readyCart('paypal_standard');
        $cartId = $this->activeCartId($customer);

        $this->get('/api/checkout/paypal-standard/'.$cartId.'/forged/success')->assertNotFound();

        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }

    public function test_initiate_returns_the_reference_and_the_return_pages(): void
    {
        $this->enablePayU();
        $customer = $this->readyCart('payu');

        $response = $this->checkoutPost('/api/shop/checkout-payments/initiate');

        $response->assertCreated();
        expect($response->json('reference'))->toBe((string) $this->activeCartId($customer));
        expect($response->json('successUrl'))->toBe(route('shop.checkout.onepage.success'));
        expect($response->json('failureUrl'))->toBe(route('shop.checkout.cart.index'));
    }

    public function test_confirm_returns_the_order_a_gateway_callback_placed(): void
    {
        $this->enableStripe();
        $customer = $this->readyCart('stripe');
        $cartId = $this->activeCartId($customer);

        $order = $this->placeOrderAsGatewayCallback($cartId);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();
        expect($response->json('paymentStatus'))->toBe('success');
        expect($response->json('success'))->toBeTrue();
        expect($response->json('orderId'))->toBe($order->id);
        expect($response->json('orderIncrementId'))->toBe((string) $order->increment_id);
    }

    public function test_confirm_reports_pending_until_the_gateway_callback_places_the_order(): void
    {
        $this->enableStripe();
        $customer = $this->readyCart('stripe');
        $cartId = $this->activeCartId($customer);

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId]);

        $response->assertCreated();
        expect($response->json('paymentStatus'))->toBe('pending');
        expect($response->json('success'))->toBeFalse();
        expect($response->json('orderId'))->toBeNull();
        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('cart', ['id' => $cartId, 'is_active' => 1]);
    }

    public function test_confirm_without_a_reference_reports_pending_for_a_callback_gateway(): void
    {
        $this->enableStripe();
        $this->readyCart('stripe');

        $response = $this->checkoutPost('/api/shop/checkout-payments/confirm');

        $response->assertCreated();
        expect($response->json('paymentStatus'))->toBe('pending');
    }

    public function test_confirm_with_the_reference_is_safe_to_repeat(): void
    {
        $this->enableStripe();
        $customer = $this->readyCart('stripe');
        $cartId = $this->activeCartId($customer);
        $this->placeOrderAsGatewayCallback($cartId);

        $first = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertCreated();
        $again = $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertCreated();

        expect($again->json('orderId'))->toBe($first->json('orderId'));
        expect($again->json('paymentStatus'))->toBe('success');
        expect(DB::table('orders')->where('cart_id', $cartId)->count())->toBe(1);
    }

    public function test_confirm_refuses_a_reference_of_another_customer(): void
    {
        $this->enableStripe();
        $owner = $this->readyCart('stripe');
        $cartId = $this->activeCartId($owner);
        $this->placeOrderAsGatewayCallback($cartId);

        $this->readyCart('stripe');

        $this->checkoutPost('/api/shop/checkout-payments/confirm', ['reference' => (string) $cartId])->assertNotFound();
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
        $smartButton = $this->fakeSmartButton($this->smartButtonOrder($cartId, customId: $cartId + 1000));

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
