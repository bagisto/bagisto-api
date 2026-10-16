<?php

namespace Webkul\BagistoApi\Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Mockery;
use Webkul\Customer\Models\Customer;
use Webkul\Paypal\Payment\SmartButton;
use Webkul\Razorpay\Payment\RazorpayPayment;

/**
 * Builds a customer cart that is ready to pay, and fakes the Razorpay and PayPal API calls.
 */
trait PreparesCheckoutPayment
{
    protected const RAZORPAY_SECRET = 'rzp_test_secret';

    protected array $checkoutHeaders = [];

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

    protected function enableRazorpay(): void
    {
        $this->enablePaymentMethod('razorpay', [
            'sandbox' => '0',
            'client_id' => 'rzp_test_key',
            'client_secret' => self::RAZORPAY_SECRET,
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

        $this->checkoutPost('/api/shop/add-product-in-cart', ['productId' => $this->createTestProduct()['product']->id, 'quantity' => 1])->assertSuccessful();

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

    protected function checkoutPost(string $url, array $data = [])
    {
        return $this->withHeaders($this->checkoutHeaders)->postJson($url, $data);
    }

    protected function activeCartId(Customer $customer): int
    {
        return (int) DB::table('cart')->where('customer_id', $customer->id)->where('is_active', 1)->value('id');
    }

    /**
     * Binds a Razorpay payment class whose API returns the given order instead of calling Razorpay.
     */
    protected function fakeRazorpayApi(array $razorpayOrder): void
    {
        $api = new class($razorpayOrder)
        {
            public object $order;

            public function __construct(array $razorpayOrder)
            {
                $this->order = new class($razorpayOrder)
                {
                    public function __construct(private array $razorpayOrder) {}

                    public function fetch(string $id): array
                    {
                        return $this->razorpayOrder;
                    }

                    public function create(array $data): array
                    {
                        return $this->razorpayOrder;
                    }
                };
            }
        };

        $razorpay = Mockery::mock(RazorpayPayment::class)->makePartial();
        $razorpay->shouldReceive('getApi')->andReturn($api);
        $razorpay->shouldReceive('isCurrencySupported')->andReturn(true);

        $this->app->instance(RazorpayPayment::class, $razorpay);
    }

    protected function razorpaySignature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId.'|'.$paymentId, self::RAZORPAY_SECRET);
    }

    protected function enableSmartButton(string $currency = 'USD'): void
    {
        $this->enablePaymentMethod('paypal_smart_button', [
            'sandbox' => '1',
            'client_id' => 'paypal_test_client',
            'client_secret' => 'paypal_test_secret',
            'accepted_currencies' => $currency,
        ]);
    }

    /**
     * The approved PayPal order core would have created for the cart.
     */
    protected function smartButtonOrder(int $cartId, string $status = 'APPROVED'): array
    {
        $cart = DB::table('cart')->where('id', $cartId)->first();

        return [
            'id' => 'PAYPAL-ORDER-1',
            'intent' => 'CAPTURE',
            'status' => $status,
            'purchase_units' => [[
                'custom_id' => (string) $cartId,
                'amount' => ['currency_code' => $cart->cart_currency_code, 'value' => number_format((float) $cart->grand_total, 2, '.', '')],
            ]],
            'payer' => ['email_address' => 'john@example.com'],
        ];
    }

    /**
     * Binds a Smart Button payment class that answers with the given PayPal order instead of calling PayPal.
     */
    protected function fakeSmartButton(array $paypalOrder, string $captureStatus = 'COMPLETED'): SmartButton
    {
        $smartButton = Mockery::mock(SmartButton::class)->makePartial();
        $smartButton->shouldReceive('createOrder')->andReturn(['id' => $paypalOrder['id'], 'status' => 'CREATED']);
        $smartButton->shouldReceive('getOrder')->andReturn($paypalOrder, ['status' => 'COMPLETED'] + $paypalOrder);
        $smartButton->shouldReceive('captureOrder')->andReturn(['id' => $paypalOrder['id'], 'status' => $captureStatus]);

        $this->app->instance(SmartButton::class, $smartButton);

        return $smartButton;
    }
}
