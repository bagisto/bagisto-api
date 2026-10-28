<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class CheckoutTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test list shipping methods requires auth.
     */
    public function test_list_shipping_methods_requires_auth(): void
    {
        $q = 'query { adminCartShippingRates(cartId: 1) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test set shipping method requires auth.
     */
    public function test_set_shipping_method_requires_auth(): void
    {
        $m = <<<'GQL'
            mutation Set($input: setShippingMethodAdminCartInput!) {
              setShippingMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => [
                'id' => '/api/admin/carts/1',
                'shippingMethod' => 'flatrate_flatrate',
            ],
        ]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test list shipping methods sequence 409 when no addresses.
     */
    public function test_list_shipping_methods_sequence_409_when_no_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $q = 'query Q($id: Int!) { adminCartShippingRates(cartId: $id) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q, ['id' => $cartId], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test set shipping method sequence 409 when no addresses.
     */
    public function test_set_shipping_method_sequence_409_when_no_addresses(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $m = <<<'GQL'
            mutation Set($input: setShippingMethodAdminCartInput!) {
              setShippingMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => [
                'id' => '/api/admin/carts/'.$cartId,
                'cartId' => $cartId,
                'shippingMethod' => 'flatrate_flatrate',
            ],
        ], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test set payment method sequence when no shipping.
     */
    public function test_set_payment_method_sequence_when_no_shipping(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $m = <<<'GQL'
            mutation Set($input: setPaymentMethodAdminCartInput!) {
              setPaymentMethodAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($m, [
            'input' => [
                'id' => '/api/admin/carts/'.$cartId,
                'cartId' => $cartId,
                'method' => 'cashondelivery',
            ],
        ], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test list payment methods requires auth.
     */
    public function test_list_payment_methods_requires_auth(): void
    {
        $q = 'query { adminCartPaymentMethods(cartId: 1) { edges { node { method } } } }';
        $resp = $this->adminGraphQL($q);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * The shipping rates query fills the multi-word fields of each rate.
     */
    public function test_list_shipping_methods_returns_multi_word_fields(): void
    {
        $cart = $this->bootstrapPlaceableCart();

        $q = 'query Q($id: Int!) { adminCartShippingRates(cartId: $id) { edges { node { method carrierCode carrierTitle methodTitle formattedPrice baseTotal formattedBaseTotal } } } }';
        $resp = $this->adminGraphQL($q, ['id' => $cart->id], $this->createAdmin());

        expect($resp->json('errors'))->toBeNull();

        $nodes = collect($resp->json('data.adminCartShippingRates.edges'))->pluck('node');

        expect($nodes)->not->toBeEmpty();

        foreach ($nodes as $node) {
            expect($node['carrierCode'])->toBeString();
            expect($node['method'])->toStartWith($node['carrierCode'].'_');
            expect($node['carrierTitle'])->toBeString();
            expect($node['methodTitle'])->toBeString();
            expect($node['formattedPrice'])->toBeString();
            expect($node['baseTotal'])->not->toBeNull();
            expect($node['formattedBaseTotal'])->toBeString();
        }
    }

    /**
     * The payment methods query fills the method title of each method.
     */
    public function test_list_payment_methods_returns_method_title(): void
    {
        $cart = $this->bootstrapPlaceableCart();

        $q = 'query Q($id: Int!) { adminCartPaymentMethods(cartId: $id) { edges { node { method methodTitle } } } }';
        $resp = $this->adminGraphQL($q, ['id' => $cart->id], $this->createAdmin());

        expect($resp->json('errors'))->toBeNull();

        $nodes = collect($resp->json('data.adminCartPaymentMethods.edges'))->pluck('node');

        expect($nodes)->not->toBeEmpty();

        foreach ($nodes as $node) {
            expect($node['methodTitle'])->toBeString();
        }
    }

    /**
     * Bootstrap draft cart.
     */
    protected function bootstrapDraftCart(): int
    {
        return $this->bootstrapAdminDraftCart();
    }
}
