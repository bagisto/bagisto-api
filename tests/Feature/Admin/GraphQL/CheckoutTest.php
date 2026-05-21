<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for Wave 3 admin checkout.
 *
 *   adminCartShippingRates(cartId:)              query collection
 *   setShippingMethodAdminCart mutation
 *   adminCartPaymentMethods(cartId:)             query collection
 *   setPaymentMethodAdminCart mutation
 *
 * Mirrors the REST CheckoutTest contract.
 */
class CheckoutTest extends AdminApiTestCase
{
    protected function bootstrapDraftCart(): ?int
    {
        $customer = Customer::query()->first();
        $product = \Webkul\Product\Models\Product::query()->where('type', 'simple')->first();

        if (! $customer || ! $product) {
            return null;
        }

        try {
            $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);
            CartFacade::setCart($cart);
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
            CartFacade::collectTotals();
        } catch (\Throwable) {
            return $cart->id ?? null;
        }

        return $cart->id;
    }

    public function test_list_shipping_methods_requires_auth(): void
    {
        $q = 'query { adminCartShippingRates(cartId: 1) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_set_shipping_method_requires_auth(): void
    {
        $m = <<<'GQL'
            mutation Set($input: setShippingMethodAdminCartInput!) {
              setShippingMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => ['id' => '/api/admin/carts/1', 'shippingMethod' => 'flatrate_flatrate'],
        ]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_list_shipping_methods_sequence_409_when_no_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        if ($cartId === null) {
            $this->markTestSkipped('No draft cart fixture.');
        }

        $q = 'query Q($id: Int!) { adminCartShippingRates(cartId: $id) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q, ['id' => $cartId], $admin);

        // Either errors[] populated (sequence guard tripped) or empty edges.
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_set_shipping_method_sequence_409_when_no_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        if ($cartId === null) {
            $this->markTestSkipped('No draft cart fixture.');
        }

        $m = <<<'GQL'
            mutation Set($input: setShippingMethodAdminCartInput!) {
              setShippingMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => $cartId, 'shippingMethod' => 'flatrate_flatrate'],
        ], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_set_payment_method_sequence_when_no_shipping(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        if ($cartId === null) {
            $this->markTestSkipped('No draft cart fixture.');
        }

        $m = <<<'GQL'
            mutation Set($input: setPaymentMethodAdminCartInput!) {
              setPaymentMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => $cartId, 'method' => 'cashondelivery'],
        ], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_list_payment_methods_requires_auth(): void
    {
        $q = 'query { adminCartPaymentMethods(cartId: 1) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q);
        expect($resp->json('errors'))->not->toBeNull();
    }
}
