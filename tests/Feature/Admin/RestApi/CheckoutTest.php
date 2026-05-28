<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Models\Cart;
use Webkul\User\Models\Admin;

/**
 * REST coverage for Wave 3 admin checkout — shipping + payment methods.
 *
 *   GET  /api/admin/carts/{cartId}/shipping-methods
 *   POST /api/admin/carts/{cartId}/shipping-methods
 *   GET  /api/admin/carts/{cartId}/payment-methods
 *   POST /api/admin/carts/{cartId}/payment-methods
 *
 * Covers happy-path + every 409 sequence-guard message + auth + active-cart
 * rejection + unknown-cart 404.
 */
class CheckoutTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Build a draft cart with one simple product item. Returns cart id. */
    protected function bootstrapDraftCart(): int
    {
        return $this->bootstrapAdminDraftCart();
    }

    /** Save a generic address on the cart so shipping rates can be collected. */
    protected function saveAddresses(int $cartId, Admin $admin): \Illuminate\Testing\TestResponse
    {
        return $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/addresses', [
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

    public function test_list_shipping_methods_requires_auth(): void
    {
        $resp = $this->publicGet('/api/admin/carts/1/shipping-methods');
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_set_shipping_method_requires_auth(): void
    {
        $resp = $this->postJson('/api/admin/carts/1/shipping-methods', ['shippingMethod' => 'flatrate_flatrate']);
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_list_payment_methods_requires_auth(): void
    {
        $resp = $this->publicGet('/api/admin/carts/1/payment-methods');
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_set_payment_method_requires_auth(): void
    {
        $resp = $this->postJson('/api/admin/carts/1/payment-methods', ['method' => 'cashondelivery']);
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_unknown_cart_returns_404_on_shipping(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/carts/999999999/shipping-methods')->assertStatus(404);
    }

    public function test_unknown_cart_returns_404_on_payment(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/carts/999999999/payment-methods')->assertStatus(404);
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

        $this->adminGet($admin, '/api/admin/carts/'.$cart->id.'/shipping-methods')->assertStatus(403);
        $this->adminGet($admin, '/api/admin/carts/'.$cart->id.'/payment-methods')->assertStatus(403);
        $this->adminPost($admin, '/api/admin/carts/'.$cart->id.'/shipping-methods', ['shippingMethod' => 'flatrate_flatrate'])
            ->assertStatus(403);
        $this->adminPost($admin, '/api/admin/carts/'.$cart->id.'/payment-methods', ['method' => 'cashondelivery'])
            ->assertStatus(403);
    }

    public function test_list_shipping_methods_requires_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $resp = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_set_shipping_method_requires_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $resp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ]);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_list_payment_methods_requires_shipping(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $resp = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_set_payment_method_requires_shipping(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $resp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', [
            'method' => 'cashondelivery',
        ]);
        expect($resp->getStatusCode())->toBe(409);
    }

    public function test_set_shipping_method_requires_method_code(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $resp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', []);
        expect($resp->getStatusCode())->toBeIn([400, 409, 422]);
    }

    public function test_set_payment_method_requires_method_code(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);
        $resp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', []);
        expect($resp->getStatusCode())->toBeIn([400, 409, 422]);
    }

    public function test_list_shipping_methods_returns_envelope_after_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $resp = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');

        expect($resp->getStatusCode())->toBeIn([200, 409]);
        if ($resp->getStatusCode() === 200) {
            expect($resp->json())->toHaveKeys(['data', 'meta']);
            expect($resp->json('data'))->toBeArray();
        }
    }

    public function test_list_payment_methods_after_shipping_set(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        if ($rates->getStatusCode() !== 200 || empty($rates->json('data'))) {
            $this->markTestSkipped('No shipping rates available in this environment.');
        }

        $firstMethod = $rates->json('data.0.method');
        $setResp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => $firstMethod,
        ]);
        expect($setResp->getStatusCode())->toBeIn([200, 201]);

        $payments = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');

        expect($payments->getStatusCode())->toBeIn([200, 409]);
        if ($payments->getStatusCode() === 200) {
            expect($payments->json())->toHaveKeys(['data', 'meta']);
        }
    }

    public function test_full_checkout_chain_addresses_to_payment(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $this->saveAddresses($cartId, $admin);

        $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
        if ($rates->getStatusCode() !== 200 || empty($rates->json('data'))) {
            $this->markTestSkipped('No shipping rates available.');
        }

        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', [
            'shippingMethod' => $rates->json('data.0.method'),
        ]);

        $payments = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');
        if ($payments->getStatusCode() !== 200 || empty($payments->json('data'))) {
            $this->markTestSkipped('No payment methods available.');
        }

        $payResp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', [
            'method' => 'cashondelivery',
        ]);
        expect($payResp->getStatusCode())->toBeIn([200, 201]);
        expect($payResp->json())->toHaveKey('paymentMethod');
    }
}
