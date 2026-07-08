<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
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

    public function test_create_validates_composite_child_inventory(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();
        $childProduct = $this->findOrCreateSimpleProduct();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'status' => 'processing',
        ]);
        OrderPayment::factory()->create([
            'order_id' => $order->id,
            'method' => 'cashondelivery',
        ]);

        // Configurable (composite) parent line with no product inventory of its
        // own — exactly like a real bundle/configurable order item (stock lives
        // on the child). Pre-fix the parent had no inventory rows so the check
        // was skipped entirely; the over-quantity shipment went through.
        $parent = OrderItem::factory()->create([
            'order_id' => $order->id,
            'parent_id' => null,
            'type' => 'configurable',
            'sku' => 'CFG-PARENT-'.uniqid(),
            'name' => 'Configurable Parent',
            'product_id' => null,
            'qty_ordered' => 1, 'qty_shipped' => 0, 'qty_invoiced' => 0,
            'qty_canceled' => 0, 'qty_refunded' => 0,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'parent_id' => $parent->id,
            'type' => 'simple',
            'sku' => 'CFG-CHILD-'.uniqid(),
            'name' => 'Configurable Child',
            'product_id' => $childProduct->id,
            'qty_ordered' => 1, 'qty_shipped' => 0, 'qty_invoiced' => 0,
            'qty_canceled' => 0, 'qty_refunded' => 0,
        ]);

        // Zero out the child product's stock at every source.
        DB::table('product_inventories')
            ->where('product_id', $childProduct->id)->delete();

        $source = (int) (DB::table('inventory_sources')->value('id') ?? 1);

        $resp = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/shipments', [
            'source' => $source,
            'items' => [
                ['orderItemId' => $parent->id, 'inventorySourceId' => $source, 'quantity' => 1],
            ],
        ]);

        // The child has no stock at the source, so the composite validation must
        // reject. Pre-fix, only the parent (which has no stock of its own) was
        // checked, so an over-quantity shipment went through.
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_view_returns_shipment_detail(): void
    {
        $shipmentId = Shipment::query()->value('id') ?? $this->bootstrapOrderWithShipment()->shipments->first()->id;
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/shipments/'.$shipmentId);
        $response->assertOk();
        expect($response->json())->toHaveKeys([
            'id', 'orderId', 'orderIncrementId', 'shippedTo', 'orderDate', 'orderStatus',
            'orderStatusLabel', 'channelName', 'customerName', 'customerEmail', 'totalQty',
            'carrierTitle', 'trackNumber', 'inventorySourceId', 'inventorySourceName',
            'paymentMethod', 'paymentTitle', 'orderCurrencyCode', 'shippingMethod',
            'shippingTitle', 'baseShippingAmount', 'formattedBaseShippingAmount',
            'billingAddress', 'shippingAddress', 'createdAt', 'updatedAt', 'items',
        ]);
        expect($response->json('id'))->toBe($shipmentId);
    }
}
