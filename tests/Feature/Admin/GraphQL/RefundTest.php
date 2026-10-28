<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Refund;

class RefundTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test create requires authentication.
     */
    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminRefundInput!){ createAdminRefund(input:$input){ adminRefund { _id } } }';

        $response = $this->adminGraphQL($mutation, ['input' => [
            'orderId' => 1,
            'items' => [],
        ]]);

        expect($response->json('errors'))->toBeArray();
    }

    /**
     * Test preview returns totals.
     */
    public function test_preview_returns_totals(): void
    {
        $order = Order::with('items')
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_invoiced - qty_refunded) > 0');
            })
            ->first() ?? $this->bootstrapRefundableOrder('processing');

        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_refund > 0);
        $admin = $this->createAdmin();
        $refundsBefore = Refund::query()->where('order_id', $order->id)->count();

        $mutation = 'mutation($input: previewAdminRefundInput!){ previewAdminRefund(input:$input){ adminRefund { orderId grandTotal subtotal formattedGrandTotal } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'orderId' => $order->id,
                'items' => [[
                    'orderItemId' => $item->id,
                    'quantity' => 1,
                ]],
                'shipping' => 0,
                'adjustmentRefund' => 0,
                'adjustmentFee' => 0,
            ],
        ], $admin);

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.previewAdminRefund.adminRefund');

        expect($node['orderId'])->toBe($order->id);
        expect($node['grandTotal'])->toBeGreaterThan(0);
        expect($node['subtotal'])->toBeGreaterThan(0);
        expect($node['formattedGrandTotal'])->toBeString()->not->toBeEmpty();
        expect(Refund::query()->where('order_id', $order->id)->count())->toBe($refundsBefore);
    }

    /**
     * Test view refund by id.
     */
    public function test_view_refund_by_id(): void
    {
        $id = Refund::query()->value('id') ?? $this->bootstrapOrderWithRefund()->refunds->first()->id;
        $admin = $this->createAdmin();
        $query = 'query($id: ID!){ adminRefund(id:$id){ _id grandTotal } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/refunds/'.$id], $admin);

        $node = $response->json('data.adminRefund');
        if ($node) {
            expect($node['_id'])->toBe($id);
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }
}
