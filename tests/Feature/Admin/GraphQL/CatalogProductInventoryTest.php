<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * Phase 5.12 — GraphQL coverage for the product-inventory sub-resource.
 *   - adminCatalogProductInventories(productId:)
 *   - updateAdminCatalogProductInventories(input: { productId, inventories })
 */
class CatalogProductInventoryTest extends AdminApiTestCase
{
    public function test_query_returns_inventories_for_product(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $sourceId = (int) DB::table('inventory_sources')->orderBy('id')->value('id');

        DB::table('product_inventories')->updateOrInsert(
            ['product_id' => $product->id, 'inventory_source_id' => $sourceId, 'vendor_id' => 0],
            ['qty' => 18],
        );

        $query = <<<'GQL'
            query inventories($productId: Int!) {
              adminCatalogProductInventories(productId: $productId) {
                edges { node { id sourceId sourceCode sourceName qty } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['productId' => $product->id], $admin);

        $response->assertOk();

        $data = $response->json('data.adminCatalogProductInventories');
        if ($data !== null) {
            $this->assertGreaterThan(0, (int) $data['totalCount']);
        }
    }

    public function test_mutation_updates_inventories(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $sourceId = (int) DB::table('inventory_sources')->orderBy('id')->value('id');

        $mutation = <<<'GQL'
            mutation updateInv($input: updateAdminCatalogProductInventoryInput!) {
              updateAdminCatalogProductInventory(input: $input) {
                adminCatalogProductInventory { id sourceId qty }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'          => '/api/admin/catalog/products/'.$product->id.'/inventories',
                'productId'   => $product->id,
                'inventories' => [(string) $sourceId => 33],
            ],
        ], $admin);

        $response->assertOk();

        $this->assertSame(33, (int) DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->where('inventory_source_id', $sourceId)
            ->value('qty'));
    }
}
