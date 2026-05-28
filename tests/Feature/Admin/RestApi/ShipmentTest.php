<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Shipment;

/**
 * REST coverage for Admin Shipment — create + view.
 */
class ShipmentTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function aShippableOrder(): Order
    {
        $existing = Order::with(['items.product'])
            ->whereNotIn('status', [Order::STATUS_CLOSED, Order::STATUS_FRAUD])
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_ordered - qty_shipped - qty_refunded - qty_canceled) > 0');
            })
            ->first();

        return $existing ?? $this->bootstrapShippableOrder('processing');
    }

    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/shipments', ['source' => 1, 'items' => []])->assertStatus(401);
    }

    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/shipments/1')->assertStatus(401);
    }

    public function test_view_returns_404_for_unknown_shipment(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/shipments/999999999')->assertStatus(404);
    }

    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/shipments', [
            'source' => 1, 'items' => [['orderItemId' => 1, 'inventorySourceId' => 1, 'quantity' => 1]],
        ])->assertStatus(404);
    }

    public function test_create_rejects_missing_source(): void
    {
        $order = $this->aShippableOrder();
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/shipments', [
            'items' => [['orderItemId' => $order->items->first()->id, 'inventorySourceId' => 1, 'quantity' => 1]],
        ]);
        $response->assertStatus(422);
    }

    public function test_view_returns_shipment_detail(): void
    {
        $shipmentId = Shipment::query()->value('id') ?? $this->bootstrapOrderWithShipment()->shipments->first()->id;
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/shipments/'.$shipmentId);
        $response->assertOk();
        expect($response->json())->toHaveKeys(['id', 'orderId', 'totalQty', 'items']);
        expect($response->json('id'))->toBe($shipmentId);
    }
}
