<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\DownloadableLinkPurchased;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerDownloadableProductTest extends GraphQLTestCase
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
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'DOWNLOAD-SKU-001',
            'type' => 'downloadable',
            'name' => 'Downloadable Test Product',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        $purchase1 = DownloadableLinkPurchased::create([
            'product_name' => 'Downloadable Test Product',
            'name' => 'Download Link 1',
            'url' => null,
            'file' => 'downloadable/test-file.pdf',
            'file_name' => 'test-file.pdf',
            'type' => 'file',
            'download_bought' => 5,
            'download_used' => 1,
            'status' => 'available',
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'order_item_id' => $order->items->first()->id,
            'download_canceled' => 0,
        ]);

        $purchase2 = DownloadableLinkPurchased::create([
            'product_name' => 'Downloadable Test Product',
            'name' => 'Download Link 2',
            'url' => 'https://example.com/download/file.zip',
            'file' => null,
            'file_name' => 'file.zip',
            'type' => 'url',
            'download_bought' => 3,
            'download_used' => 3,
            'status' => 'expired',
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'order_item_id' => $order->items->first()->id,
            'download_canceled' => 0,
        ]);

        $purchase3 = DownloadableLinkPurchased::create([
            'product_name' => 'Downloadable Test Product',
            'name' => 'Download Link 3',
            'url' => null,
            'file' => 'downloadable/pending-file.pdf',
            'file_name' => 'pending-file.pdf',
            'type' => 'file',
            'download_bought' => 10,
            'download_used' => 0,
            'status' => 'pending',
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'order_item_id' => $order->items->first()->id,
            'download_canceled' => 0,
        ]);

        return compact('customer', 'channel', 'product', 'order', 'purchase1', 'purchase2', 'purchase3');
    }

    // ── Collection Queries ────────────────────────────────────

    /**
     * Test: Query all customer downloadable products collection
     */
    public function test_get_customer_downloadable_products_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 10) {
                edges {
                  cursor
                  node {
                    _id
                    productName
                    name
                    fileName
                    type
                    downloadBought
                    downloadUsed
                    downloadCanceled
                    status
                    createdAt
                  }
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(3);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Unauthenticated request returns error
     */
    public function test_get_customer_downloadable_products_requires_authentication(): void
    {
        $query = <<<'GQL'
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 5) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Customer only sees their own downloadable products
     */
    public function test_customer_only_sees_own_downloadable_products(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own downloadable purchase */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_email' => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name' => $otherCustomer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $product = Product::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $otherOrder->id,
            'product_id' => $product->id,
            'sku' => 'OTHER-DOWNLOAD',
            'type' => 'downloadable',
            'name' => 'Other Download',
        ]);

        DownloadableLinkPurchased::create([
            'product_name' => 'Other Download',
            'name' => 'Other Link',
            'type' => 'file',
            'file' => 'downloadable/other.pdf',
            'file_name' => 'other.pdf',
            'download_bought' => 5,
            'download_used' => 0,
            'status' => 'available',
            'customer_id' => $otherCustomer->id,
            'order_id' => $otherOrder->id,
            'order_item_id' => $otherOrder->items->first()->id,
            'download_canceled' => 0,
        ]);

        $query = <<<'GQL'
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 50) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        /** Should only see the 3 purchases belonging to testData customer */
        expect($data['totalCount'])->toBe(3);
    }

    /**
     * Test: Filter downloadable products by status
     */
    public function test_filter_downloadable_products_by_status(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerDownloadableProducts($status: String) {
              customerDownloadableProducts(first: 10, status: $status) {
                edges {
                  node {
                    _id
                    status
                  }
                }
                totalCount
              }
            }
        GQL;

        /** Filter for 'available' — should get 1 result */
        $response = $this->authenticatedGraphQL($testData['customer'], $query, ['status' => 'available']);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        expect($data['totalCount'])->toBe(1);
        expect($data['edges'][0]['node']['status'])->toBe('available');
    }

    /**
     * Test: Filter downloadable products by expired status
     */
    public function test_filter_downloadable_products_by_expired_status(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerDownloadableProducts($status: String) {
              customerDownloadableProducts(first: 10, status: $status) {
                edges {
                  node {
                    _id
                    status
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, ['status' => 'expired']);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        expect($data['totalCount'])->toBe(1);
        expect($data['edges'][0]['node']['status'])->toBe('expired');
    }

    /**
     * Test: Cursor-based pagination works
     */
    public function test_cursor_pagination(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 1) {
                edges {
                  cursor
                  node {
                    _id
                  }
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(3);
        expect($data['edges'])->toHaveCount(1);
        expect($data['pageInfo']['hasNextPage'])->toBeTrue();

        /** Fetch next page using the endCursor */
        $cursor = $data['pageInfo']['endCursor'];

        $nextQuery = <<<GQL
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 1, after: "$cursor") {
                edges {
                  node {
                    _id
                  }
                }
                pageInfo {
                  hasNextPage
                }
                totalCount
              }
            }
        GQL;

        $nextResponse = $this->authenticatedGraphQL($testData['customer'], $nextQuery);

        $nextResponse->assertOk();
        $nextData = $nextResponse->json('data.customerDownloadableProducts');

        expect($nextData['edges'])->toHaveCount(1);
    }

    /**
     * Test: Query single item by ID
     */
    public function test_get_single_downloadable_product(): void
    {
        $testData = $this->createTestData();

        $query = <<<GQL
            query getCustomerDownloadableProduct {
              customerDownloadableProduct(id: "/api/shop/customer-downloadable-products/{$testData['purchase1']->id}") {
                _id
                productName
                name
                fileName
                type
                downloadBought
                downloadUsed
                status
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProduct');

        expect($data)->not()->toBeNull();
        expect($data['productName'])->toBe('Downloadable Test Product');
        expect($data['name'])->toBe('Download Link 1');
        expect($data['type'])->toBe('file');
        expect($data['status'])->toBe('available');
    }

    /**
     * Test: Customer with no downloadable products returns empty collection
     */
    public function test_customer_with_no_downloadable_products_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $query = <<<'GQL'
            query getCustomerDownloadableProducts {
              customerDownloadableProducts(first: 10) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        $response->assertOk();
        $data = $response->json('data.customerDownloadableProducts');

        expect($data['totalCount'])->toBe(0);
        expect($data['edges'])->toBeEmpty();
    }
}
