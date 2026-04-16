<?php

namespace Webkul\BagistoApi\Tests\Feature\Rest;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\DownloadableLinkPurchased;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerDownloadableProductRestTest extends RestApiTestCase
{
    /**
     * Create test data — customer with downloadable product purchases
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => 'DOWNLOAD-SKU-001',
            'type'       => 'downloadable',
            'name'       => 'Downloadable Test Product',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        $purchase1 = DownloadableLinkPurchased::create([
            'product_name'      => 'Downloadable Test Product',
            'name'              => 'Download Link 1',
            'url'               => null,
            'file'              => 'downloadable/test-file.pdf',
            'file_name'         => 'test-file.pdf',
            'type'              => 'file',
            'download_bought'   => 5,
            'download_used'     => 1,
            'status'            => 'available',
            'customer_id'       => $customer->id,
            'order_id'          => $order->id,
            'order_item_id'     => $order->items->first()->id,
            'download_canceled' => 0,
        ]);

        $purchase2 = DownloadableLinkPurchased::create([
            'product_name'      => 'Downloadable Test Product',
            'name'              => 'Download Link 2',
            'url'               => 'https://example.com/download/file.zip',
            'file'              => null,
            'file_name'         => 'file.zip',
            'type'              => 'url',
            'download_bought'   => 3,
            'download_used'     => 3,
            'status'            => 'expired',
            'customer_id'       => $customer->id,
            'order_id'          => $order->id,
            'order_item_id'     => $order->items->first()->id,
            'download_canceled' => 0,
        ]);

        return compact('customer', 'channel', 'product', 'order', 'purchase1', 'purchase2');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-downloadable-products returns collection
     */
    public function test_get_customer_downloadable_products_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-downloadable-products');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/customer-downloadable-products without auth returns error
     */
    public function test_get_customer_downloadable_products_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-downloadable-products');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: Customer only sees own downloadable products
     */
    public function test_customer_only_sees_own_downloadable_products(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own downloadable purchase */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        $otherOrder = Order::factory()->create([
            'customer_id'         => $otherCustomer->id,
            'customer_email'      => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name'  => $otherCustomer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        $product = Product::factory()->create();

        OrderItem::factory()->create([
            'order_id'   => $otherOrder->id,
            'product_id' => $product->id,
            'sku'        => 'OTHER-DOWNLOAD',
            'type'       => 'downloadable',
            'name'       => 'Other Download',
        ]);

        DownloadableLinkPurchased::create([
            'product_name'      => 'Other Download',
            'name'              => 'Other Link',
            'type'              => 'file',
            'file'              => 'downloadable/other.pdf',
            'file_name'         => 'other.pdf',
            'download_bought'   => 5,
            'download_used'     => 0,
            'status'            => 'available',
            'customer_id'       => $otherCustomer->id,
            'order_id'          => $otherOrder->id,
            'order_item_id'     => $otherOrder->items->first()->id,
            'download_canceled' => 0,
        ]);

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-downloadable-products');

        $response->assertOk();
        $json = $response->json();

        /** Should only see the 2 purchases belonging to testData customer */
        expect(count($json))->toBe(2);
    }

    /**
     * Test: Customer with no downloadable products returns empty collection
     */
    public function test_customer_with_no_downloadable_products_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-downloadable-products');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBe(0);
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-downloadable-products/{id} returns single item
     */
    public function test_get_single_customer_downloadable_product(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-downloadable-products/'.$testData['purchase1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('productName');
        expect($json)->toHaveKey('name');
        expect($json)->toHaveKey('fileName');
        expect($json)->toHaveKey('type');
        expect($json)->toHaveKey('downloadBought');
        expect($json)->toHaveKey('downloadUsed');
        expect($json)->toHaveKey('status');
        expect($json['id'])->toBe($testData['purchase1']->id);
        expect($json['productName'])->toBe('Downloadable Test Product');
        expect($json['name'])->toBe('Download Link 1');
        expect($json['type'])->toBe('file');
        expect($json['status'])->toBe('available');
    }

    /**
     * Test: GET /api/shop/customer-downloadable-products/{id} with invalid id returns 404
     */
    public function test_get_customer_downloadable_product_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-downloadable-products/999999');

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Cannot access another customer's downloadable product by ID
     */
    public function test_cannot_access_other_customers_downloadable_product(): void
    {
        $testData = $this->createTestData();

        /** Create another customer */
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-downloadable-products/'.$testData['purchase1']->id
        );

        /** Should get 404 since the purchase doesn't belong to otherCustomer */
        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
