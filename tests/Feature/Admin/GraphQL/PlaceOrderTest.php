<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Facades\Cart as CartFacade;

class PlaceOrderTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * The mutation.
     */
    private string $mutation = <<<'GQL'
        mutation Place($input: createAdminPlaceOrderInput!) {
          createAdminPlaceOrder(input: $input) {
            adminPlaceOrder { id }
          }
        }
    GQL;

    /**
     * Test requires auth.
     */
    public function test_requires_auth(): void
    {
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => 1]]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test unknown cart errors.
     */
    public function test_unknown_cart_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => 999999999]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test empty cart sequence error.
     */
    public function test_empty_cart_sequence_error(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();

        $cart = CartFacade::createCart([
            'customer' => $customer,
            'is_active' => false,
        ]);

        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => $cart->id]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test no addresses sequence error.
     */
    public function test_no_addresses_sequence_error(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();
        $product = $this->findOrCreateSimpleProduct();

        $cart = CartFacade::createCart([
            'customer' => $customer,
            'is_active' => false,
        ]);
        CartFacade::setCart($cart);
        try {
            CartFacade::addProduct($product, [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        } catch (\Throwable) {
        }

        $resp = $this->adminGraphQL($this->mutation, ['input' => ['cartId' => $cart->id]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }
}
