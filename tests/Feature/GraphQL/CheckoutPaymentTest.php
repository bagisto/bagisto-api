<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\Concerns\PreparesCheckoutPayment;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CheckoutPaymentTest extends GraphQLTestCase
{
    use PreparesCheckoutPayment;

    private function checkoutGraphQL(string $query, array $variables = [])
    {
        return $this->graphQL($query, $variables, $this->checkoutHeaders);
    }

    public function test_initiate_paypal_standard_returns_the_paypal_form(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
        $this->readyCart('paypal_standard');

        $response = $this->checkoutGraphQL(<<<'GQL'
            mutation initiate($input: createCheckoutPaymentInitiationInput!) {
              createCheckoutPaymentInitiation(input: $input) {
                checkoutPaymentInitiation {
                  method
                  type
                  formAction
                  formFields
                }
              }
            }
        GQL, ['input' => (object) []]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createCheckoutPaymentInitiation.checkoutPaymentInitiation.type'))->toBe('form');
        expect($response->json('data.createCheckoutPaymentInitiation.checkoutPaymentInitiation.formFields.business'))->toBe('merchant@example.com');
    }

    public function test_initiate_returns_the_reference_and_the_return_pages(): void
    {
        $this->enablePayU();
        $customer = $this->readyCart('payu');

        $response = $this->checkoutGraphQL(<<<'GQL'
            mutation initiate($input: createCheckoutPaymentInitiationInput!) {
              createCheckoutPaymentInitiation(input: $input) {
                checkoutPaymentInitiation {
                  type
                  reference
                  successUrl
                  failureUrl
                }
              }
            }
        GQL, ['input' => (object) []]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $initiation = $response->json('data.createCheckoutPaymentInitiation.checkoutPaymentInitiation');

        expect($initiation['reference'])->toBe((string) $this->activeCartId($customer));
        expect($initiation['successUrl'])->toBe(route('shop.checkout.onepage.success'));
        expect($initiation['failureUrl'])->toBe(route('shop.checkout.cart.index'));
    }

    public function test_confirm_reads_the_order_a_gateway_callback_placed(): void
    {
        $this->enableStripe();
        $customer = $this->readyCart('stripe');
        $cartId = $this->activeCartId($customer);

        $mutation = <<<'GQL'
            mutation confirm($input: createCheckoutPaymentConfirmationInput!) {
              createCheckoutPaymentConfirmation(input: $input) {
                checkoutPaymentConfirmation {
                  orderId
                  orderIncrementId
                  paymentStatus
                  success
                  message
                }
              }
            }
        GQL;

        $pending = $this->checkoutGraphQL($mutation, ['input' => ['reference' => (string) $cartId]]);

        $pending->assertOk();
        expect($pending->json('errors'))->toBeNull();
        expect($pending->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.paymentStatus'))->toBe('pending');
        expect($pending->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.orderId'))->toBeNull();

        $order = $this->placeOrderAsGatewayCallback($cartId);

        $confirmed = $this->checkoutGraphQL($mutation, ['input' => ['reference' => (string) $cartId]]);

        $confirmed->assertOk();
        expect($confirmed->json('errors'))->toBeNull();

        $confirmation = $confirmed->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation');

        expect($confirmation['paymentStatus'])->toBe('success');
        expect($confirmation['orderId'])->toBe($order->id);
        expect($confirmation['orderIncrementId'])->toBe((string) $order->increment_id);
    }

    public function test_initiate_refuses_an_offline_payment_method(): void
    {
        $this->readyCart('cashondelivery');

        $response = $this->checkoutGraphQL(<<<'GQL'
            mutation initiate($input: createCheckoutPaymentInitiationInput!) {
              createCheckoutPaymentInitiation(input: $input) {
                checkoutPaymentInitiation {
                  type
                }
              }
            }
        GQL, ['input' => (object) []]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_confirm_razorpay_places_a_paid_order(): void
    {
        $this->enableRazorpay();
        $customer = $this->readyCart('razorpay');
        $cartId = $this->activeCartId($customer);
        $amount = (int) (DB::table('cart')->where('id', $cartId)->value('base_grand_total') * 100);
        $this->fakeRazorpayApi(['id' => 'order_test_1', 'notes' => ['cart_id' => $cartId], 'amount' => $amount]);

        $response = $this->checkoutGraphQL(<<<'GQL'
            mutation confirm($input: createCheckoutPaymentConfirmationInput!) {
              createCheckoutPaymentConfirmation(input: $input) {
                checkoutPaymentConfirmation {
                  orderId
                  orderIncrementId
                  status
                  success
                }
              }
            }
        GQL, ['input' => [
            'razorpayOrderId' => 'order_test_1',
            'razorpayPaymentId' => 'pay_test_1',
            'razorpaySignature' => $this->razorpaySignature('order_test_1', 'pay_test_1'),
        ]]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.status'))->toBe('processing');
        $this->assertDatabaseHas('orders', ['cart_id' => $cartId]);
    }

    public function test_smart_button_initiate_and_confirm_place_a_paid_order(): void
    {
        $customer = $this->readyCart('paypal_smart_button');
        $cartId = $this->activeCartId($customer);
        $this->enableSmartButton((string) DB::table('cart')->where('id', $cartId)->value('cart_currency_code'));
        $this->fakeSmartButton($this->smartButtonOrder($cartId));

        $initiate = $this->checkoutGraphQL(<<<'GQL'
            mutation initiate($input: createCheckoutPaymentInitiationInput!) {
              createCheckoutPaymentInitiation(input: $input) {
                checkoutPaymentInitiation {
                  method
                  type
                  sdkData
                }
              }
            }
        GQL, ['input' => ['paymentMethod' => 'paypal_smart_button']]);

        $initiate->assertOk();
        expect($initiate->json('errors'))->toBeNull();
        expect($initiate->json('data.createCheckoutPaymentInitiation.checkoutPaymentInitiation.type'))->toBe('sdk');
        expect($initiate->json('data.createCheckoutPaymentInitiation.checkoutPaymentInitiation.sdkData.order_id'))->toBe('PAYPAL-ORDER-1');

        $confirm = $this->checkoutGraphQL(<<<'GQL'
            mutation confirm($input: createCheckoutPaymentConfirmationInput!) {
              createCheckoutPaymentConfirmation(input: $input) {
                checkoutPaymentConfirmation {
                  orderId
                  orderIncrementId
                  status
                  success
                }
              }
            }
        GQL, ['input' => ['paypalOrderId' => 'PAYPAL-ORDER-1']]);

        $confirm->assertOk();
        expect($confirm->json('errors'))->toBeNull();
        expect($confirm->json('data.createCheckoutPaymentConfirmation.checkoutPaymentConfirmation.status'))->toBe('processing');
        $this->assertDatabaseHas('orders', ['cart_id' => $cartId]);
        $this->assertDatabaseHas('order_transactions', ['transaction_id' => 'PAYPAL-ORDER-1']);
    }

    public function test_smart_button_confirm_refuses_a_paypal_order_of_another_cart(): void
    {
        $customer = $this->readyCart('paypal_smart_button');
        $cartId = $this->activeCartId($customer);
        $this->enableSmartButton((string) DB::table('cart')->where('id', $cartId)->value('cart_currency_code'));
        $this->fakeSmartButton($this->smartButtonOrder($cartId, customId: $cartId + 1000));

        $response = $this->checkoutGraphQL(<<<'GQL'
            mutation confirm($input: createCheckoutPaymentConfirmationInput!) {
              createCheckoutPaymentConfirmation(input: $input) {
                checkoutPaymentConfirmation {
                  orderId
                }
              }
            }
        GQL, ['input' => ['paypalOrderId' => 'PAYPAL-ORDER-1']]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseMissing('orders', ['cart_id' => $cartId]);
    }
}
