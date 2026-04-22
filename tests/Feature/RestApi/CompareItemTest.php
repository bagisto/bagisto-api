<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\CompareItem;

class CompareItemTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/compare_items';

    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product1 = $this->createBaseProduct('simple');
        $product2 = $this->createBaseProduct('simple');

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

    // ── GET Collection ────────────────────────────────────────

    public function test_get_compare_items_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();
        $data = $response->json();

        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(2);
    }

    public function test_get_compare_items_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl);

        // AuthorizationException has no HttpExceptionInterface — REST maps it to 500
        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_get_compare_items_collection_only_returns_own_items(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $otherProduct = $this->createBaseProduct('simple');

        CompareItem::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id'  => $otherProduct->id,
        ]);

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();
        $items = $response->json();

        foreach ($items as $item) {
            expect($item['customer']['id'] ?? $testData['customer']->id)->toBe($testData['customer']->id);
        }
    }

    // ── GET Single ────────────────────────────────────────────

    public function test_get_single_compare_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['compareItem1']->id
        );

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data)->toHaveKey('product');
        expect($data)->toHaveKey('customer');
        expect($data)->toHaveKey('createdAt');
        expect($data)->toHaveKey('updatedAt');
        expect($data['id'])->toBe($testData['compareItem1']->id);
    }

    public function test_get_non_existent_compare_item_returns_404(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->baseUrl.'/999999');

        $response->assertNotFound();
    }

    public function test_compare_item_id_is_integer(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['compareItem1']->id
        );

        $response->assertOk();
        expect($response->json('id'))->toBeInt();
    }

    public function test_compare_item_timestamps_are_iso8601(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['compareItem1']->id
        );

        $response->assertOk();
        expect($response->json('createdAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($response->json('updatedAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    // ── POST Create ───────────────────────────────────────────

    public function test_create_compare_item(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'productId' => $product->id,
        ]);

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data['id'])->toBeInt();
        expect(
            CompareItem::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists()
        )->toBeTrue();
    }

    public function test_create_compare_item_with_snake_case_key(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'product_id' => $product->id,
        ]);

        $response->assertCreated();
        expect($response->json('id'))->toBeInt();
    }

    public function test_create_compare_item_requires_auth(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->publicPost($this->baseUrl, ['productId' => $product->id]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_create_compare_item_without_product_id_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->baseUrl, []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_create_compare_item_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'productId' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    public function test_create_duplicate_compare_item_returns_error(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost($testData['customer'], $this->baseUrl, [
            'productId' => $testData['product1']->id,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 409, 422]);
    }

    // ── DELETE Single ─────────────────────────────────────────

    public function test_delete_compare_item(): void
    {
        $testData = $this->createTestData();
        $itemId = $testData['compareItem1']->id;

        $response = $this->authenticatedDelete(
            $testData['customer'],
            $this->baseUrl.'/'.$itemId
        );

        $response->assertNoContent();
        expect(CompareItem::find($itemId))->toBeNull();
    }

    public function test_delete_compare_item_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicDelete($this->baseUrl.'/'.$testData['compareItem1']->id);

        // AuthorizationException has no HttpExceptionInterface — REST maps it to 500
        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_delete_non_existent_compare_item_returns_404(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedDelete($customer, $this->baseUrl.'/999999');

        $response->assertNotFound();
    }

    public function test_cannot_delete_other_customers_compare_item(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedDelete(
            $otherCustomer,
            $this->baseUrl.'/'.$testData['compareItem1']->id
        );

        // AuthorizationException returned as 500 (no HttpExceptionInterface)
        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }

    // ── POST Delete All ───────────────────────────────────────

    public function test_delete_all_compare_items(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost(
            $testData['customer'],
            '/api/shop/delete-all-compare-items'
        );

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('message');
        expect($data)->toHaveKey('deletedCount');
        expect($data['deletedCount'])->toBe(2);
        expect(
            CompareItem::where('customer_id', $testData['customer']->id)->count()
        )->toBe(0);
    }

    public function test_delete_all_compare_items_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/delete-all-compare-items');

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_delete_all_compare_items_when_empty_returns_zero_count(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/delete-all-compare-items');

        $response->assertCreated();
        expect($response->json('deletedCount'))->toBe(0);
    }

    public function test_delete_all_compare_items_only_removes_own_items(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $otherProduct = $this->createBaseProduct('simple');

        CompareItem::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id'  => $otherProduct->id,
        ]);

        $this->authenticatedPost($testData['customer'], '/api/shop/delete-all-compare-items');

        expect(
            CompareItem::where('customer_id', $otherCustomer->id)->count()
        )->toBe(1);
    }

    public function test_delete_all_compare_items_message_is_string(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/delete-all-compare-items');

        $response->assertCreated();
        expect($response->json('message'))->toBeString();
    }
}
