<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\Concerns\PreparesCheckoutPayment;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CheckoutPaymentTest extends GraphQLTestCase
{
    use PreparesCheckoutPayment;

    /**
     * The mutation that starts a payment.
     */
    private string $initiate = <<<'GQL'
        mutation initiate($input: createCheckoutPaymentSessionInput!) {
          createCheckoutPaymentSession(input: $input) {
            checkoutPaymentSession {
              success
              cartId
              method
              type
              reference
              formAction
              formFields
              sdkData
              successUrl
              failureUrl
              message
            }
          }
        }
    GQL;

    /**
     * The mutation that confirms a payment.
     */
    private string $confirm = <<<'GQL'
        mutation confirm($input: createCheckoutPaymentConfirmationInput!) {
          createCheckoutPaymentConfirmation(input: $input) {
            checkoutPaymentConfirmation {
              success
              cartId
              orderId
              orderIncrementId
              status
              paymentStatus
              message
            }
          }
        }
    GQL;

    /**
     * Test initiate paypal standard returns the paypal form.
     */
    public function test_initiate_paypal_standard_returns_the_paypal_form(): void
    {
        $this->enablePaypalStandard();
        $cartId = $this->activeCartId($this->readyCart('paypal_standard'));

        $response = $this->checkoutGraphQL($this->initiate, ['input' => (object) []]);

        $response->assertOk();

        $session = $response->json('data.createCheckoutPaymentSession.checkoutPaymentSession');

        expect($response->json('errors'))->toBeNull()
            ->and($session['type'])->toBe('form')
            ->and($session['cartId'])->toBe($cartId)
            ->and($session['reference'])->toBe((string) $cartId)
            ->and($session['formFields']['business'])->toBe('merchant@example.com')
            ->and($session['successUrl'])->toBe(route('shop.checkout.onepage.success'))
            ->and($session['failureUrl'])->toBe(route('shop.checkout.cart.index'));
    }

    /**
     * Test initiate refuses an offline payment method.
     */
    public function test_initiate_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $response = $this->checkoutGraphQL($this->initiate, ['input' => (object) []]);

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test confirm reads the order a gateway callback placed.
     */
    public function test_confirm_reads_the_order_a_gateway_callback_placed(): void
    {
        $this->enableStripe();
        $cartId = $this->activeCartId($this->readyCart('stripe'));

        $pending = $this->checkoutGraphQL($this->confirm, ['input' => ['reference' => (string) $cartId]]);

        expect($pending->json('errors'))->toBeNull()
            ->and($pending->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.paymentStatus'))->toBe('pending')
            ->and($pending->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.orderId'))->toBeNull();

        $order = $this->placeOrderAsGatewayCallback($cartId);

        $confirmed = $this->checkoutGraphQL($this->confirm, ['input' => ['reference' => (string) $cartId]]);

        $confirmation = $confirmed->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation');

        expect($confirmed->json('errors'))->toBeNull()
            ->and($confirmation['paymentStatus'])->toBe('success')
            ->and($confirmation['cartId'])->toBe($cartId)
            ->and($confirmation['orderId'])->toBe((string) $order->id)
            ->and($confirmation['orderIncrementId'])->toBe((string) $order->increment_id);
    }

    /**
     * Test confirm razorpay places a paid order.
     */
    public function test_confirm_razorpay_places_a_paid_order(): void
    {
        $this->enableRazorpay();
        $cartId = $this->activeCartId($this->readyCart('razorpay'));

        DB::table('cart')->where('id', $cartId)->update(['global_currency_code' => 'INR', 'base_currency_code' => 'INR']);

        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId]], $this->razorpayPayment($cartId, 'order_test_1'));

        $response = $this->checkoutGraphQL($this->confirm, ['input' => [
            'razorpayOrderId' => 'order_test_1',
            'razorpayPaymentId' => 'pay_test_1',
            'razorpaySignature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ]]);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.status'))->toBe('processing')
            ->and($response->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.paymentStatus'))->toBe('success');

        $this->assertDatabaseHas('orders', ['cart_id' => $cartId]);
    }

    /**
     * Test smart button initiate and confirm place a paid order.
     */
    public function test_smart_button_initiate_and_confirm_place_a_paid_order(): void
    {
        $cartId = $this->readySmartButtonCart();
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $initiate = $this->checkoutGraphQL($this->initiate, ['input' => ['paymentMethod' => 'paypal_smart_button']]);

        expect($initiate->json('errors'))->toBeNull()
            ->and($initiate->json('data.createCheckoutPaymentSession.checkoutPaymentSession.type'))->toBe('sdk')
            ->and($initiate->json('data.createCheckoutPaymentSession.checkoutPaymentSession.sdkData.orderId'))->toBe('PAYPAL-ORDER-1');

        $confirm = $this->checkoutGraphQL($this->confirm, ['input' => ['paypalOrderId' => 'PAYPAL-ORDER-1']]);

        expect($confirm->json('errors'))->toBeNull()
            ->and($confirm->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.status'))->toBe('processing');

        $this->assertDatabaseHas('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('order_transactions', ['transaction_id' => 'PAYPAL-ORDER-1']);
    }

    /**
     * Send a GraphQL request as the customer whose cart is being paid.
     */
    private function checkoutGraphQL(string $query, array $variables = []): TestResponse
    {
        return $this->graphQL($query, $variables, $this->checkoutHeaders);
    }
}
