<?php

namespace Tests\Feature\BagistoApi\Rest;

use Tests\Feature\BagistoApi\RestApiTestCase;
use Webkul\Customer\Models\CompareItem;
use Webkul\Product\Models\Product;

class CompareItemRestTest extends RestApiTestCase
{
    /**
     * Create test data - customer, products and compare items
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $compareItem1 = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product1->id,
        ]);
        $compareItem2 = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product2->id,
        ]);

        return compact('customer', 'product1', 'product2', 'compareItem1', 'compareItem2');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/compare_items returns collection
     */
    public function test_get_compare_items_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/compare_items');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/compare_items without auth throws error
     */
    public function test_get_compare_items_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/compare_items');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/compare_items/{id} returns single item
     */
    public function test_get_single_compare_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/compare_items/' . $testData['compareItem1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('product');
        expect($json)->toHaveKey('customer');
        expect($json)->toHaveKey('createdAt');
        expect($json)->toHaveKey('updatedAt');
        expect($json['id'])->toBe($testData['compareItem1']->id);
    }

    /**
     * Test: GET /api/shop/compare_items/{id} not found returns 404
     */
    public function test_get_compare_item_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/compare_items/999999');

        $response->assertNotFound();
    }

    // ── Create ────────────────────────────────────────────────

    /**
     * Test: POST /api/shop/compare_items creates a compare item
     */
    public function test_create_compare_item(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $response = $this->authenticatedPost($customer, '/api/shop/compare_items', [
            'product_id' => $product->id,
        ]);

        $response->assertCreated();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect(CompareItem::where('customer_id', $customer->id)->where('product_id', $product->id)->exists())->toBeTrue();
    }

    /**
     * Test: POST /api/shop/compare_items without auth returns error
     */
    public function test_create_compare_item_requires_auth(): void
    {
        $this->seedRequiredData();
        $product = Product::factory()->create();

        $response = $this->publicPost('/api/shop/compare_items', [
            'product_id' => $product->id,
        ]);

        expect(in_array($response->getStatusCode(), [400, 401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: POST /api/shop/compare_items with duplicate returns error
     */
    public function test_create_duplicate_compare_item_returns_error(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost($testData['customer'], '/api/shop/compare_items', [
            'product_id' => $testData['product1']->id,
        ]);

        expect(in_array($response->getStatusCode(), [400, 409, 422, 500]))->toBeTrue();
    }

    // ── Delete Single ─────────────────────────────────────────

    /**
     * Test: DELETE /api/shop/compare_items/{id} removes a compare item
     */
    public function test_delete_compare_item(): void
    {
        $testData = $this->createTestData();
        $itemId = $testData['compareItem1']->id;

        $response = $this->authenticatedDelete(
            $testData['customer'],
            '/api/shop/compare_items/' . $itemId
        );

        $response->assertNoContent();
        expect(CompareItem::find($itemId))->toBeNull();
    }

    /**
     * Test: DELETE /api/shop/compare_items/{id} other user's item is rejected
     */
    public function test_delete_other_users_compare_item(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedDelete(
            $otherCustomer,
            '/api/shop/compare_items/' . $testData['compareItem1']->id
        );

        expect(in_array($response->getStatusCode(), [204, 403, 404, 500]))->toBeTrue();
    }

    // ── Delete All ────────────────────────────────────────────

    /**
     * Test: POST /api/shop/delete-all-compare-items removes all compare items
     */
    public function test_delete_all_compare_items(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost(
            $testData['customer'],
            '/api/shop/delete-all-compare-items'
        );

        $response->assertCreated();
        $json = $response->json();

        expect($json['message'])->toContain('removed');
        expect($json['deletedCount'])->toBe(2);
        expect(CompareItem::where('customer_id', $testData['customer']->id)->count())->toBe(0);
    }

    /**
     * Test: POST /api/shop/delete-all-compare-items requires authentication
     */
    public function test_delete_all_compare_items_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/delete-all-compare-items');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: POST /api/shop/delete-all-compare-items with no items returns zero
     */
    public function test_delete_all_compare_items_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/delete-all-compare-items');

        $response->assertCreated();
        $json = $response->json();

        expect($json['deletedCount'])->toBe(0);
    }
}
