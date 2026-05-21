<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for Wave 3 place-order mutation.
 */
class PlaceOrderTest extends AdminApiTestCase
{
    private string $mutation = <<<'GQL'
        mutation Place($input: createAdminPlaceOrderInput!) {
          createAdminPlaceOrder(input: $input) {
            adminPlaceOrder { id }
          }
        }
    GQL;

    public function test_requires_auth(): void
    {
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => 1]]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_unknown_cart_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => 999999999]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_empty_cart_sequence_error(): void
    {
        $admin = $this->createAdmin();
        $customer = Customer::query()->first();
        if (! $customer) {
            $this->markTestSkipped('No customer fixture.');
        }

        $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);

        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => $cart->id]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_no_addresses_sequence_error(): void
    {
        $admin = $this->createAdmin();
        $customer = Customer::query()->first();
        $product = \Webkul\Product\Models\Product::query()->where('type', 'simple')->first();
        if (! $customer || ! $product) {
            $this->markTestSkipped('Fixtures missing.');
        }

        try {
            $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);
            CartFacade::setCart($cart);
            CartFacade::addProduct($product, ['product_id' => $product->id, 'quantity' => 1]);
        } catch (\Throwable) {
            $this->markTestSkipped('Could not seed cart.');
        }

        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => $cart->id]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }
}
