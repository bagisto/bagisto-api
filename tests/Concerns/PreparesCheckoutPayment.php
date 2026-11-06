<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Paypal\Payment\SmartButton;
use Webkul\Razorpay\Payment\RazorpayPayment;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

trait PreparesCheckoutPayment
{
    /**
     * The Razorpay secret the fake payments are signed with.
     */
    protected string $razorpaySecret = 'rzp_test_secret';

    /**
     * The headers of the customer whose cart is being paid.
     */
    protected array $checkoutHeaders = [];

    /**
     * Turn a payment method on with the given settings for the current channel and locale.
     */
    protected function enablePaymentMethod(string $method, array $settings = []): void
    {
        foreach (['active' => '1', ...$settings] as $field => $value) {
            $code = "sales.payment_methods.{$method}.{$field}";

            DB::table('core_config')->where('code', $code)->delete();

            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    /**
     * Turn Razorpay on with live-mode test credentials.
     */
    protected function enableRazorpay(): void
    {
        $this->enablePaymentMethod('razorpay', [
            'sandbox' => '0',
            'client_id' => 'rzp_test_key',
            'client_secret' => $this->razorpaySecret,
        ]);
    }

    /**
     * Turn Stripe on with test credentials.
     */
    protected function enableStripe(): void
    {
        $this->enablePaymentMethod('stripe', [
            'sandbox' => '0',
            'api_key' => 'sk_test_key',
            'api_publishable_key' => 'pk_test_key',
        ]);
    }

    /**
     * Turn PayU on with sandbox credentials.
     */
    protected function enablePayU(): void
    {
        $this->enablePaymentMethod('payu', [
            'sandbox' => '1',
            'merchant_key' => 'payu_test_key',
            'merchant_salt' => 'payu_test_salt',
        ]);
    }

    /**
     * Turn PayPal Standard on with a merchant account.
     */
    protected function enablePaypalStandard(): void
    {
        $this->enablePaymentMethod('paypal_standard', ['business_account' => 'merchant@example.com']);
    }

    /**
     * Turn the PayPal Smart Button on, accepting the given currency.
     */
    protected function enableSmartButton(string $currency): void
    {
        $this->enablePaymentMethod('paypal_smart_button', [
            'sandbox' => '1',
            'client_id' => 'paypal_test_client',
            'client_secret' => 'paypal_test_secret',
            'accepted_currencies' => $currency,
        ]);
    }

    /**
     * A customer whose cart has an item, addresses, flat-rate shipping and the given payment method.
     */
    protected function readyCart(string $paymentMethod): Customer
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $this->checkoutHeaders = $this->authHeaders($customer);

        $this->checkoutPost('/api/shop/add-product-in-cart', [
            'productId' => $this->createTestProduct()['product']->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->checkoutPost('/api/shop/checkout-addresses', [
            'billingFirstName' => 'John',
            'billingLastName' => 'Doe',
            'billingEmail' => 'john@example.com',
            'billingAddress' => '123 Main St',
            'billingCity' => 'Los Angeles',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
            'useForShipping' => true,
        ])->assertSuccessful();

        $this->checkoutPost('/api/shop/checkout-shipping-methods', ['shippingMethod' => 'flatrate_flatrate'])->assertSuccessful();

        $this->checkoutPost('/api/shop/checkout-payment-methods', ['paymentMethod' => $paymentMethod])->assertSuccessful();

        return $customer;
    }

    /**
     * A cart paying by PayPal Smart Button, with the method accepting the cart currency.
     */
    protected function readySmartButtonCart(): int
    {
        $this->enableSmartButton('USD');

        $cartId = $this->activeCartId($this->readyCart('paypal_smart_button'));

        $this->enableSmartButton((string) DB::table('cart')->where('id', $cartId)->value('cart_currency_code'));

        return $cartId;
    }

    /**
     * Post as the customer whose cart is being paid.
     */
    protected function checkoutPost(string $url, array $data = []): TestResponse
    {
        return $this->withHeaders($this->checkoutHeaders)->postJson($url, $data);
    }

    /**
     * The customer's active cart.
     */
    protected function activeCartId(Customer $customer): int
    {
        return (int) DB::table('cart')->where('customer_id', $customer->id)->where('is_active', 1)->value('id');
    }

    /**
     * Place the cart's order and close the cart, as a Stripe, PhonePe, PayU or PayGlocal callback does once paid.
     */
    protected function placeOrderAsGatewayCallback(int $cartId): Order
    {
        Cart::setCart(app(CartRepository::class)->find($cartId));

        Cart::collectTotals();

        $order = app(OrderRepository::class)->create((new OrderResource(Cart::getCart()))->jsonSerialize());

        Cart::deActivateCart();

        return $order;
    }

    /**
     * Bind a Razorpay payment class whose API answers with the given order and payment instead of calling Razorpay.
     */
    protected function fakeRazorpayApi(array $razorpayOrder, array $payment = []): void
    {
        $api = new class($razorpayOrder, $payment)
        {
            /**
             * The fake orders endpoint.
             */
            public object $order;

            /**
             * The fake payments endpoint.
             */
            public object $payment;

            /**
             * Create the fake API.
             */
            public function __construct(array $razorpayOrder, array $payment)
            {
                $this->order = new class($razorpayOrder)
                {
                    /**
                     * Create the fake orders endpoint.
                     */
                    public function __construct(private array $razorpayOrder) {}

                    /**
                     * The Razorpay order.
                     */
                    public function fetch(string $id): array
                    {
                        return $this->razorpayOrder;
                    }

                    /**
                     * Create the Razorpay order.
                     */
                    public function create(array $data): array
                    {
                        return $this->razorpayOrder;
                    }
                };

                $this->payment = new class($payment)
                {
                    /**
                     * Create the fake payments endpoint.
                     */
                    public function __construct(private array $payment) {}

                    /**
                     * The Razorpay payment.
                     */
                    public function fetch(string $id): object
                    {
                        return (object) $this->payment;
                    }
                };
            }
        };

        $razorpay = Mockery::mock(RazorpayPayment::class)->makePartial();
        $razorpay->shouldReceive('getApi')->andReturn($api);

        $this->app->instance(RazorpayPayment::class, $razorpay);
    }

    /**
     * The captured Razorpay payment of the cart's full base total.
     */
    protected function razorpayPayment(int $cartId, string $razorpayOrderId): array
    {
        $cart = DB::table('cart')->where('id', $cartId)->first();

        return [
            'order_id' => $razorpayOrderId,
            'amount' => (int) round($cart->base_grand_total * 100),
            'currency' => $cart->base_currency_code,
            'status' => 'captured',
        ];
    }

    /**
     * Bind a gateway's payment class whose listed methods answer as given, so no gateway is called.
     */
    protected function fakeGateway(string $class, array $answers): MockInterface
    {
        $gateway = Mockery::mock($class)->makePartial();

        foreach (['isAvailable' => true, 'hasValidCredentials' => true, ...$answers] as $method => $answer) {
            $gateway->shouldReceive($method)->andReturn($answer);
        }

        $this->app->instance($class, $gateway);

        return $gateway;
    }

    /**
     * The signature Razorpay returns for the payment.
     */
    protected function razorpaySignature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId.'|'.$paymentId, $this->razorpaySecret);
    }

    /**
     * The approved PayPal order created for the cart; pass another cart id to fake an order made for it.
     */
    protected function smartButtonOrder(int $cartId, string $status = 'APPROVED', ?int $customId = null): array
    {
        $cart = DB::table('cart')->where('id', $cartId)->first();

        return [
            'id' => 'PAYPAL-ORDER-1',
            'intent' => 'CAPTURE',
            'status' => $status,
            'purchase_units' => [[
                'custom_id' => (string) ($customId ?? $cartId),
                'amount' => [
                    'currency_code' => $cart->cart_currency_code,
                    'value' => number_format((float) $cart->grand_total, 2, '.', ''),
                ],
            ]],
            'payer' => ['email_address' => 'john@example.com'],
        ];
    }

    /**
     * Bind a Smart Button payment class that answers with the given PayPal order instead of calling PayPal.
     */
    protected function fakeSmartButton(array $paypalOrder, string $captureStatus = 'COMPLETED'): MockInterface
    {
        $smartButton = Mockery::mock(SmartButton::class)->makePartial();
        $smartButton->shouldReceive('createOrder')->andReturn(['id' => $paypalOrder['id'], 'status' => 'CREATED']);
        $smartButton->shouldReceive('getOrder')->andReturn($paypalOrder, ['status' => 'COMPLETED'] + $paypalOrder);
        $smartButton->shouldReceive('captureOrder')->andReturn(['id' => $paypalOrder['id'], 'status' => $captureStatus]);

        $this->app->instance(SmartButton::class, $smartButton);

        return $smartButton;
    }
}
