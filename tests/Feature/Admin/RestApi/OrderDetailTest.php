<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin Order detail — GET /api/admin/orders/{id}.
 */
class OrderDetailTest extends AdminApiTestCase
{
    /** Resolve an existing order id from the listing, or skip. */
    protected function anOrderId(): ?int
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');

        return empty($rows) ? null : $rows[0]['id'];
    }

    public function test_detail_requires_authentication(): void
    {
        $this->publicGet('/api/admin/orders/1')->assertStatus(401);
    }

    public function test_detail_returns_the_full_order_payload(): void
    {
        $id = $this->anOrderId();

        if ($id === null) {
            $this->markTestSkipped('No orders in the database.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/orders/'.$id);

        $response->assertOk();
        expect($response->json())->toHaveKeys([
            'id', 'incrementId', 'status', 'statusLabel', 'grandTotal',
            'customer', 'billingAddress', 'shippingAddress',
            'items', 'invoices', 'shipments',
        ]);
        expect($response->json('id'))->toBe($id);
        expect($response->json('items'))->toBeArray();
    }

    public function test_detail_items_carry_the_product_type(): void
    {
        $id = $this->anOrderId();

        if ($id === null) {
            $this->markTestSkipped('No orders in the database.');
        }

        $admin = $this->createAdmin();
        $items = $this->adminGet($admin, '/api/admin/orders/'.$id)->json('items');

        if (empty($items)) {
            $this->markTestSkipped('Order has no items.');
        }

        // Type discriminator + type-specific slots must be present.
        expect($items[0])->toHaveKeys(['id', 'sku', 'type', 'name', 'qtyOrdered', 'additional', 'children']);
    }

    public function test_detail_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/orders/999999999')->assertStatus(404);
    }
}
