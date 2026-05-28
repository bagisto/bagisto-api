<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * Phase 5.12 — REST tests for product-inventory sub-resource.
 *
 *   GET /api/admin/catalog/products/{productId}/inventories
 *   PUT /api/admin/catalog/products/{productId}/inventories
 */
class CatalogProductInventoryTest extends AdminApiTestCase
{
    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Returns the first inventory_source id (Bagisto seeds at least one row).
     */
    protected function firstSourceId(): int
    {
        return (int) DB::table('inventory_sources')->orderBy('id')->value('id');
    }

    /**
     * Returns a second inventory_source id, creating one if only one exists.
     */
    protected function secondSourceId(): int
    {
        $ids = DB::table('inventory_sources')->orderBy('id')->limit(2)->pluck('id')->all();

        if (count($ids) >= 2) {
            return (int) $ids[1];
        }

        return (int) DB::table('inventory_sources')->insertGetId([
            'code'        => 'secondary-'.uniqid(),
            'name'        => 'Secondary Source',
            'description' => 'Test secondary source',
            'priority'    => 2,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    protected function seedInventory(int $productId, int $sourceId, int $qty): void
    {
        DB::table('product_inventories')->updateOrInsert(
            ['product_id' => $productId, 'inventory_source_id' => $sourceId, 'vendor_id' => 0],
            ['qty' => $qty],
        );
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    public function test_list_inventories_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $sourceId = $this->firstSourceId();

        $this->seedInventory($product->id, $sourceId, 25);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id.'/inventories');

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
        $this->assertArrayHasKey('totalQty', $body['meta']);
        $this->assertSame(25, (int) $body['meta']['totalQty']);

        $row = $body['data'][0];
        $this->assertArrayHasKey('sourceId', $row);
        $this->assertArrayHasKey('sourceCode', $row);
        $this->assertArrayHasKey('sourceName', $row);
        $this->assertArrayHasKey('qty', $row);
        $this->assertSame($sourceId, (int) $row['sourceId']);
        $this->assertSame(25, (int) $row['qty']);
    }

    public function test_list_inventories_empty_returns_empty_data_and_zero_total(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id.'/inventories');

        $response->assertOk();
        $body = $response->json();

        $this->assertSame([], $body['data']);
        $this->assertSame(0, (int) $body['meta']['totalQty']);
    }

    public function test_list_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products/9999999/inventories');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_list_requires_admin_token(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->publicGet('/api/admin/catalog/products/'.$product->id.'/inventories');

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Update — happy path
    // -------------------------------------------------------------------------

    public function test_update_inventories_add_update_and_zero_out(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $s1 = $this->firstSourceId();
        $s2 = $this->secondSourceId();

        // Seed initial state: source 1 has 10
        $this->seedInventory($product->id, $s1, 10);

        // Bulk-update: change s1 to 25, add s2 with qty 5
        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => [(string) $s1 => 25, (string) $s2 => 5],
        ]);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
        $this->assertSame(30, (int) $body['meta']['totalQty']);

        // Confirm DB state
        $this->assertSame(25, (int) DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->where('inventory_source_id', $s1)
            ->value('qty'));
        $this->assertSame(5, (int) DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->where('inventory_source_id', $s2)
            ->value('qty'));

        // Zero-out s2
        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => [(string) $s2 => 0],
        ]);

        $response->assertOk();
        $body = $response->json();

        // s2 row was either zeroed or removed; either way its qty is 0
        $s2Qty = DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->where('inventory_source_id', $s2)
            ->value('qty');
        $this->assertTrue($s2Qty === null || (int) $s2Qty === 0);

        // s1 untouched
        $this->assertSame(25, (int) DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->where('inventory_source_id', $s1)
            ->value('qty'));
    }

    // -------------------------------------------------------------------------
    // Update — validation
    // -------------------------------------------------------------------------

    public function test_update_with_missing_inventories_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', []);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_update_with_unknown_source_id_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => ['999999' => 10],
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_update_with_negative_qty_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $s1 = $this->firstSourceId();

        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => [(string) $s1 => -5],
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_update_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $s1 = $this->firstSourceId();

        $response = $this->adminPut($admin, '/api/admin/catalog/products/9999999/inventories', [
            'inventories' => [(string) $s1 => 10],
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Auth + permission
    // -------------------------------------------------------------------------

    public function test_update_requires_admin_token(): void
    {
        $product = $this->createBaseProduct('simple');
        $s1 = $this->firstSourceId();

        $response = $this->putJson('/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => [(string) $s1 => 10],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_update_without_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products.view'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');
        $s1 = $this->firstSourceId();

        $response = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id.'/inventories', [
            'inventories' => [(string) $s1 => 10],
        ]);

        $this->assertSame(403, $response->getStatusCode());
    }
}
