<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class CheckoutShippingEstimateTest extends RestApiTestCase
{
    /**
     * Test estimate shipping returns the current cart estimate.
     */
    public function test_estimate_shipping_returns_the_current_cart_estimate(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/estimate-shippings', [
            'country' => 'US',
            'state' => 'CA',
            'postcode' => '90001',
        ]);

        $response->assertCreated();

        expect((int) $response->json('id'))->toBeGreaterThan(0)
            ->and((int) $response->json('itemsCount'))->toBe(1)
            ->and((float) $response->json('grandTotal'))->toBeGreaterThan(0);
    }

    /**
     * Test estimate shipping requires the full address.
     */
    public function test_estimate_shipping_requires_the_full_address(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/estimate-shippings', [
            'country' => 'US',
            'postcode' => '90001',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    /**
     * Test estimate shipping requires a cart token.
     */
    public function test_estimate_shipping_requires_a_cart_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/estimate-shippings', [
            'country' => 'US',
            'state' => 'CA',
            'postcode' => '90001',
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 404]);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    /**
     * Create authenticated customer.
     */
    private function createAuthenticatedCustomer(): Customer
    {
        $this->seedRequiredData();

        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
    }

    /**
     * Add product to cart.
     */
    private function addProductToCart(Customer $customer): void
    {
        $product = $this->createTestProduct()['product'];

        $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();
    }
}
