<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Refund;

/**
 * REST coverage for Admin Refund — create / preview / view.
 */
class RefundTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function aRefundableOrder(): Order
    {
        $existing = Order::with(['items'])
            ->whereNotIn('status', [Order::STATUS_CLOSED, Order::STATUS_FRAUD])
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_invoiced - qty_refunded) > 0');
            })
            ->first();

        return $existing ?? $this->bootstrapRefundableOrder('processing');
    }

    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/refunds', ['items' => []])->assertStatus(401);
    }

    public function test_preview_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/refunds/preview', ['items' => []])->assertStatus(401);
    }

    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/refunds/1')->assertStatus(401);
    }

    public function test_view_returns_404_for_unknown_refund(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/refunds/999999999')->assertStatus(404);
    }

    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/refunds', ['items' => []])
            ->assertStatus(404);
    }

    public function test_create_rejects_closed_orders(): void
    {
        $id = Order::query()->value('id') ?? $this->bootstrapAdminOrder('pending', false)->id;
        Order::where('id', $id)->update(['status' => Order::STATUS_CLOSED]);

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/refunds', ['items' => []]);
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.refund.closed'));
    }

    public function test_preview_returns_totals(): void
    {
        $order = $this->aRefundableOrder();
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_refund > 0);

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/refunds/preview', [
            'items'             => [['orderItemId' => $item->id, 'quantity' => 1]],
            'shipping'          => 0,
            'adjustmentRefund'  => 0,
            'adjustmentFee'     => 0,
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json())->toHaveKeys(['subtotal', 'tax', 'shipping', 'grandTotal']);
    }

    public function test_view_returns_refund_detail(): void
    {
        $refundId = Refund::query()->value('id') ?? $this->bootstrapOrderWithRefund()->refunds->first()->id;
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds/'.$refundId);
        $response->assertOk();
        expect($response->json())->toHaveKeys([
            'id', 'orderId', 'state', 'baseSubTotal', 'baseSubTotalInclTax',
            'baseTaxAmount', 'baseDiscountAmount', 'baseShippingAmount',
            'adjustmentRefund', 'baseAdjustmentRefund', 'adjustmentFee', 'baseAdjustmentFee',
            'customerName', 'customerEmail', 'channelName', 'orderStatus', 'orderStatusLabel', 'orderDate',
            'paymentMethod', 'paymentTitle', 'shippingMethod', 'shippingTitle',
            'billingAddress', 'shippingAddress', 'items',
        ]);
        expect($response->json('id'))->toBe($refundId);

        $items = $response->json('items');
        if (! empty($items)) {
            expect($items[0])->toBeArray();
            expect($items[0])->toHaveKeys(['id', 'sku', 'name', 'qty', 'basePriceInclTax', 'baseImageUrl']);
        }
    }
}
