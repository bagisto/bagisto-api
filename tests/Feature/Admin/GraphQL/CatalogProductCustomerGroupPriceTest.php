<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\CustomerGroup;

/**
 * GraphQL coverage for Phase 5.13 — admin product customer-group prices CRUD.
 *
 * Operations:
 *   adminCatalogProductCustomerGroupPrices(productId:)
 *   createAdminCatalogProductCustomerGroupPrice
 *   updateAdminCatalogProductCustomerGroupPrice
 *   deleteAdminCatalogProductCustomerGroupPrice
 */
class CatalogProductCustomerGroupPriceTest extends AdminApiTestCase
{
    protected function seedRow(int $productId, int $qty, ?int $groupId): int
    {
        return DB::table('product_customer_group_prices')->insertGetId([
            'product_id' => $productId,
            'qty' => $qty,
            'value_type' => 'fixed',
            'value' => 10.0,
            'customer_group_id' => $groupId,
            'unique_id' => implode('|', array_filter([(string) $qty, (string) $productId, $groupId === null ? null : (string) $groupId])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_query_list_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();
        $this->seedRow($product->id, 1, $group->id);
        $this->seedRow($product->id, 10, null);

        $query = <<<'GQL'
            query($productId: Int!) {
              adminCatalogProductCustomerGroupPrices(productId: $productId) {
                _id
                productId
                qty
                valueType
                value
                customerGroupId
                customerGroupName
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['productId' => $product->id], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $rows = $response->json('data.adminCatalogProductCustomerGroupPrices');
        expect($rows)->toBeArray();
        expect(count($rows))->toBe(2);

        $byQty = collect($rows)->keyBy('qty');

        $groupRow = $byQty[1];
        expect($groupRow['_id'])->not->toBeNull();
        expect($groupRow['productId'])->toBe($product->id);
        expect($groupRow['valueType'])->toBe('fixed');
        expect((float) $groupRow['value'])->toBe(10.0);
        expect($groupRow['customerGroupId'])->toBe($group->id);
        expect($groupRow['customerGroupName'])->not->toBeNull();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductCustomerGroupPriceInput!) {
              createAdminCatalogProductCustomerGroupPrice(input: $input) {
                adminCatalogProductCustomerGroupPrice {
                  _id
                  productId
                  qty
                  valueType
                  value
                  customerGroupId
                  customerGroupName
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'productId' => $product->id,
                'qty' => 7,
                'valueType' => 'fixed',
                'value' => 22.5,
                'customerGroupId' => $group->id,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminCatalogProductCustomerGroupPrice.adminCatalogProductCustomerGroupPrice');
        expect($node['_id'])->not->toBeNull();
        expect($node['productId'])->toBe($product->id);
        expect($node['qty'])->toBe(7);
        expect($node['valueType'])->toBe('fixed');
        expect((float) $node['value'])->toBe(22.5);
        expect($node['customerGroupId'])->toBe($group->id);
        expect($node['customerGroupName'])->not->toBeNull();

        $this->assertDatabaseHas('product_customer_group_prices', [
            'product_id' => $product->id,
            'qty' => 7,
            'value_type' => 'fixed',
            'customer_group_id' => $group->id,
        ]);
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();
        $rowId = $this->seedRow($product->id, 4, $group->id);

        $mutation = <<<'GQL'
            mutation($input: updateAdminCatalogProductCustomerGroupPriceInput!) {
              updateAdminCatalogProductCustomerGroupPrice(input: $input) {
                adminCatalogProductCustomerGroupPrice {
                  _id
                  productId
                  qty
                  valueType
                  value
                  customerGroupId
                  customerGroupName
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => '/api/admin/catalog/products/'.$product->id.'/customer-group-prices/'.$rowId,
                'productId' => $product->id,
                'qty' => 9,
                'valueType' => 'discount',
                'value' => 5.0,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.updateAdminCatalogProductCustomerGroupPrice.adminCatalogProductCustomerGroupPrice');
        expect($node['_id'])->not->toBeNull();
        expect($node['productId'])->toBe($product->id);
        expect($node['qty'])->toBe(9);
        expect($node['valueType'])->toBe('discount');
        expect((float) $node['value'])->toBe(5.0);
        expect($node['customerGroupId'])->toBe($group->id);
        expect($node['customerGroupName'])->not->toBeNull();

        $this->assertDatabaseHas('product_customer_group_prices', [
            'id' => $rowId,
            'qty' => 9,
            'value_type' => 'discount',
        ]);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $group = CustomerGroup::where('code', 'general')->first();
        $rowId = $this->seedRow($product->id, 4, $group->id);

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCatalogProductCustomerGroupPriceInput!) {
              deleteAdminCatalogProductCustomerGroupPrice(input: $input) {
                adminCatalogProductCustomerGroupPrice {
                  _id
                  productId
                  qty
                  valueType
                  value
                  customerGroupId
                  customerGroupName
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => '/api/admin/catalog/products/'.$product->id.'/customer-group-prices/'.$rowId,
                'productId' => $product->id,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.deleteAdminCatalogProductCustomerGroupPrice.adminCatalogProductCustomerGroupPrice');
        expect($node['_id'])->toBe($rowId);
        expect($node['productId'])->toBe($product->id);
        expect($node['qty'])->toBe(4);
        expect($node['valueType'])->toBe('fixed');
        expect($node['customerGroupId'])->toBe($group->id);
        expect($node['customerGroupName'])->not->toBeNull();

        $this->assertDatabaseMissing('product_customer_group_prices', ['id' => $rowId]);
    }
}
