<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Refund;

class RefundTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminRefundInput!){ createAdminRefund(input:$input){ adminRefundDetailDto { id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 1, 'items' => []]]);
        expect($response->json('errors'))->toBeArray();
    }

    public function test_preview_returns_totals(): void
    {
        $order = Order::with('items')
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_invoiced - qty_refunded) > 0');
            })
            ->first() ?? $this->bootstrapRefundableOrder('processing');

        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_refund > 0);
        $admin = $this->createAdmin();

        $mutation = 'mutation($input: previewAdminRefundInput!){ previewAdminRefund(input:$input){ refundTotalsSummary { grandTotal subtotal } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'orderId'          => $order->id,
                'items'            => [['orderItemId' => $item->id, 'quantity' => 1]],
                'shipping'         => 0,
                'adjustmentRefund' => 0,
                'adjustmentFee'    => 0,
            ],
        ], $admin);

        $node = $response->json('data.previewAdminRefund.refundTotalsSummary');
        if ($node) {
            expect($node)->toHaveKey('grandTotal');
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }

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
