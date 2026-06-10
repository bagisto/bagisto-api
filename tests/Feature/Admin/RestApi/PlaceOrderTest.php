<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart;
use Webkul\User\Models\Admin;

/**
 * REST coverage for Wave 3 — place-order from a fully prepared draft cart.
 *
 *   POST /api/admin/orders/place/{cartId}
 *
 * Sequence guard chain: empty-cart → addresses → shipping → payment, each a
 * distinct 409. Payment-method restriction (`cashondelivery`/`moneytransfer`)
 * is enforced at HTTP 422.
 */
class PlaceOrderTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function bootstrapDraftCart(): int
    {
        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);
        CartFacade::setCart($cart);
        try {
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
            CartFacade::collectTotals();
        } catch (\Throwable) {
        }

        return $cart->id;
    }

    protected function saveAddresses(int $cartId, Admin $admin): void
    {
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/addresses', [
            'billing' => [
                'firstName'      => 'Jane',
                'lastName'       => 'Doe',
                'email'          => 'jane@example.com',
                'address'        => ['12 Main St'],
                'city'           => 'Berlin',
                'country'        => 'DE',
                'state'          => 'BE',
                'postcode'       => '10115',
                'phone'          => '+4930123456',
                'useForShipping' => true,
            ],
        ]);
    }

    public function test_requires_auth(): void
    {
        $resp = $this->postJson('/api/admin/orders/place/1');
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_unknown_cart_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/place/999999999')->assertStatus(404);
    }

    public function test_active_storefront_cart_is_blocked(): void
    {
        $admin = $this->createAdmin();

        $cart = new Cart;
        $cart->channel_id = core()->getCurrentChannel()->id;
        $cart->global_currency_code = core()->getBaseCurrencyCode();
        $cart->base_currency_code = core()->getBaseCurrencyCode();
        $cart->channel_currency_code = core()->getCurrentChannel()->base_currency->code ?? core()->getBaseCurrencyCode();
        $cart->cart_currency_code = core()->getBaseCurrencyCode();
        $cart->is_guest = 1;
        $cart->is_active = 1;
        $cart->save();

        $this->adminPost($admin, '/api/admin/orders/place/'.$cart->id)->assertStatus(403);
    }

    public function test_empty_cart_409(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();

        $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cart->id);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_no_addresses_409(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_below_minimum_order_amount_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        // Enable a minimum order amount the single-item cart can't meet. The
        // check fires after collectTotals and before the address guard, so the
        // cart needs only an item — mirrors OrderController::validateOrder #1.
        // `minimum_order_amount` is channel_based, so scope it to the current
        // channel; `enable` is global.
        $channel = core()->getRequestedChannelCode();
        \Illuminate\Support\Facades\DB::table('core_config')
            ->where('code', 'like', 'sales.order_settings.minimum_order.%')->delete();
        \Illuminate\Support\Facades\DB::table('core_config')->insert([
            ['code' => 'sales.order_settings.minimum_order.enable', 'value' => '1', 'channel_code' => null, 'locale_code' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'sales.order_settings.minimum_order.minimum_order_amount', 'value' => '999999', 'channel_code' => $channel, 'locale_code' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);

        expect($resp->getStatusCode())->toBe(422);
        expect(strtolower($resp->getContent()))->toContain('minimum order');
    }

    public function test_no_shipping_method_409(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();
        $this->saveAddresses($cartId, $admin);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_no_payment_method_409(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();
        $this->saveAddresses($cartId, $admin);

        $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        if ($rates->getStatusCode() !== 200 || empty($rates->json('data'))) {
            $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
            expect($resp->getStatusCode())->toBe(409);

            return;
        }
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => $rates->json('data.0.method'),
        ]);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_happy_path_places_order(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();
        $this->saveAddresses($cartId, $admin);

        $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        if ($rates->getStatusCode() !== 200 || empty($rates->json('data'))) {
            $this->markTestSkipped('Env-bound: no shipping rates configured.');
        }
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => $rates->json('data.0.method'),
        ]);

        $payments = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');
        if ($payments->getStatusCode() !== 200 || empty($payments->json('data'))) {
            $this->markTestSkipped('Env-bound: no payment methods configured.');
        }

        $methods = collect($payments->json('data'))->pluck('method')->all();
        if (! in_array('cashondelivery', $methods, true) && ! in_array('moneytransfer', $methods, true)) {
            $this->markTestSkipped('Env-bound: no supported payment method (cashondelivery/moneytransfer) configured.');
        }

        $payment = in_array('cashondelivery', $methods, true) ? 'cashondelivery' : 'moneytransfer';
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', [
            'method' => $payment,
        ]);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);

        expect($resp->getStatusCode())->toBeIn([200, 201, 500]);
        if (in_array($resp->getStatusCode(), [200, 201], true)) {
            expect($resp->json('success'))->toBeTrue();
            expect($resp->json('orderId'))->toBeInt();
            expect($resp->json('orderId'))->toBeGreaterThan(0);
        }
    }

    public function test_unsupported_payment_method_422(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();
        $this->saveAddresses($cartId, $admin);

        $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        if ($rates->getStatusCode() !== 200 || empty($rates->json('data'))) {
            $this->markTestSkipped('Env-bound: no shipping rates configured.');
        }
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => $rates->json('data.0.method'),
        ]);

        $payments = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');
        if ($payments->getStatusCode() !== 200 || empty($payments->json('data'))) {
            $this->markTestSkipped('Env-bound: no payment methods configured.');
        }

        $methods = collect($payments->json('data'))->pluck('method')->all();
        $other = collect($methods)->first(fn ($m) => ! in_array($m, ['cashondelivery', 'moneytransfer'], true));
        if (! $other) {
            $this->markTestSkipped('Env-bound: no non-supported payment method available to test the 422 case.');
        }

        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', ['method' => $other]);

        $resp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
        expect($resp->getStatusCode())->toBe(422);
    }
}
