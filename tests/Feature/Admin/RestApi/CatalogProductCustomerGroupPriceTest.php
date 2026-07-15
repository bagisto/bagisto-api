<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\User\Models\Admin;

/**
 * REST coverage for Phase 5.13 — admin product customer-group prices CRUD.
 *
 * Endpoints:
 *   GET    /api/admin/catalog/products/{productId}/customer-group-prices
 *   POST   /api/admin/catalog/products/{productId}/customer-group-prices
 *   PUT    /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 *   DELETE /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 */
class CatalogProductCustomerGroupPriceTest extends AdminApiTestCase
{
    protected function adminPut(Admin $admin, string $url, array $data = []): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(Admin $admin, string $url): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    protected function seedRow(int $productId, int $qty, ?int $groupId, string $valueType = 'fixed', float $value = 10.0): int
    {
        return DB::table('product_customer_group_prices')->insertGetId([
            'product_id' => $productId,
            'qty' => $qty,
            'value_type' => $valueType,
            'value' => $value,
            'customer_group_id' => $groupId,
            'unique_id' => implode('|', array_filter([(string) $qty, (string) $productId, $groupId === null ? null : (string) $groupId])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_list_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();

        $this->seedRow($product->id, 1, $group->id, 'fixed', 19.99);
        $this->seedRow($product->id, 10, null, 'discount', 15.0);

        $response = $this->adminGet($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices");

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect(count($response->json('data')))->toBe(2);
        expect($response->json('meta'))->toBeArray();

        $first = $response->json('data.0');
        expect($first)->toHaveKeys(['id', 'productId', 'qty', 'valueType', 'value', 'customerGroupId', 'customerGroupName']);
    }

    public function test_list_for_nonexistent_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products/9999999/customer-group-prices');

        $response->assertStatus(404);
    }

    public function test_list_requires_auth(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->publicGet("/api/admin/catalog/products/{$product->id}/customer-group-prices");

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_fixed_with_group_returns_201(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 5,
            'value_type' => 'fixed',
            'value' => 12.5,
            'customer_group_id' => $group->id,
        ]);

        $response->assertStatus(201);
        expect($response->json('qty'))->toBe(5);
        expect($response->json('valueType'))->toBe('fixed');
        expect((float) $response->json('value'))->toBe(12.5);
        expect($response->json('customerGroupId'))->toBe($group->id);
        expect($response->json('productId'))->toBe($product->id);

        $this->assertDatabaseHas('product_customer_group_prices', [
            'product_id' => $product->id,
            'qty' => 5,
            'value_type' => 'fixed',
            'customer_group_id' => $group->id,
        ]);
    }

    public function test_create_discount_without_group_returns_201(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 10,
            'value_type' => 'discount',
            'value' => 20.0,
            'customer_group_id' => null,
        ]);

        $response->assertStatus(201);
        expect($response->json('customerGroupId'))->toBeNull();
        expect($response->json('valueType'))->toBe('discount');

        $row = DB::table('product_customer_group_prices')->where('id', $response->json('id'))->first();
        expect($row->unique_id)->toBe('10|'.$product->id);
    }

    public function test_create_duplicate_qty_group_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();

        $this->seedRow($product->id, 5, $group->id);

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 5,
            'value_type' => 'fixed',
            'value' => 9.0,
            'customer_group_id' => $group->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_qty_less_than_one_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 0,
            'value_type' => 'fixed',
            'value' => 10.0,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_invalid_value_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 1,
            'value_type' => 'percent',
            'value' => 10.0,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_unknown_customer_group_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 1,
            'value_type' => 'fixed',
            'value' => 10.0,
            'customer_group_id' => 9999999,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_on_nonexistent_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/products/9999999/customer-group-prices', [
            'qty' => 1,
            'value_type' => 'fixed',
            'value' => 10.0,
        ]);

        $response->assertStatus(404);
    }

    public function test_create_requires_auth(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->publicPost("/api/admin/catalog/products/{$product->id}/customer-group-prices", [
            'qty' => 1,
            'value_type' => 'fixed',
            'value' => 10.0,
        ]);

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    protected function publicPost(string $url, array $data = []): TestResponse
    {
        return $this->postJson($url, $data);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();
        $rowId = $this->seedRow($product->id, 5, $group->id, 'fixed', 10.0);

        $response = $this->adminPut($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices/{$rowId}", [
            'qty' => 8,
            'value_type' => 'discount',
            'value' => 25.0,
        ]);

        $response->assertOk();
        expect($response->json('qty'))->toBe(8);
        expect($response->json('valueType'))->toBe('discount');
        expect((float) $response->json('value'))->toBe(25.0);

        $row = DB::table('product_customer_group_prices')->where('id', $rowId)->first();
        expect((int) $row->qty)->toBe(8);
        expect($row->unique_id)->toBe('8|'.$product->id.'|'.$group->id);
    }

    public function test_update_violates_uniqueness_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();
        $this->seedRow($product->id, 5, $group->id);
        $rowB = $this->seedRow($product->id, 10, $group->id);

        $response = $this->adminPut($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices/{$rowB}", [
            'qty' => 5,
        ]);

        $response->assertStatus(422);
    }

    public function test_update_on_cross_product_row_returns_404(): void
    {
        $admin = $this->createAdmin();
        $productA = $this->createBaseProduct('simple', ['sku' => 'cgp-a-'.uniqid()]);
        $productB = $this->createBaseProduct('simple', ['sku' => 'cgp-b-'.uniqid()]);
        $rowOnA = $this->seedRow($productA->id, 5, null);

        $response = $this->adminPut($admin, "/api/admin/catalog/products/{$productB->id}/customer-group-prices/{$rowOnA}", [
            'value' => 99.0,
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $rowId = $this->seedRow($product->id, 5, null);

        $response = $this->adminDelete($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices/{$rowId}");

        $response->assertOk();
        expect($response->json('message'))->toBeString();

        $this->assertDatabaseMissing('product_customer_group_prices', ['id' => $rowId]);
    }

    public function test_delete_on_cross_product_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $productA = $this->createBaseProduct('simple', ['sku' => 'cgp-del-a-'.uniqid()]);
        $productB = $this->createBaseProduct('simple', ['sku' => 'cgp-del-b-'.uniqid()]);
        $rowOnA = $this->seedRow($productA->id, 5, null);

        $response = $this->adminDelete($admin, "/api/admin/catalog/products/{$productB->id}/customer-group-prices/{$rowOnA}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('product_customer_group_prices', ['id' => $rowOnA]);
    }

    public function test_delete_nonexistent_row_returns_404(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminDelete($admin, "/api/admin/catalog/products/{$product->id}/customer-group-prices/9999999");

        $response->assertStatus(404);
    }
}
