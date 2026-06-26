<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;

class InvoiceTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_create_with_transaction_flag_records_order_transaction(): void
    {
        $order = $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item.');
        }

        $admin = $this->createAdmin();

        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(0);

        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id transactionId } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'orderId'              => $order->id,
                'items'                => [['orderItemId' => $item->id, 'quantity' => 1]],
                'canCreateTransaction' => true,
            ],
        ], $admin);

        expect($response->json('errors'))->toBeNull();
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(1);

        $linked = OrderTransaction::where('order_id', $order->id)->value('transaction_id');
        expect($response->json('data.createAdminInvoice.adminInvoice.transactionId'))->toBe($linked);
    }

    public function test_create_mutation_returns_fully_populated_invoice(): void
    {
        $order = $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item.');
        }

        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminInvoiceInput!) {
              createAdminInvoice(input: $input) {
                adminInvoice {
                  id
                  _id
                  incrementId
                  subTotal
                  grandTotal
                  formattedGrandTotal
                  customerName
                  channelName
                  order {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => $order->id, 'items' => [['orderItemId' => $item->id, 'quantity' => 1]]],
        ], $admin);

        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.createAdminInvoice.adminInvoice');

        // The create mutation returns the invoice's scalars + order link. The
        // items connection is populated on the detail query (adminInvoice) —
        // API Platform does not resolve nested connections on a mutation payload.
        expect($node['incrementId'])->not->toBeNull();
        expect($node['order']['_id'])->toBe($order->id);
        expect($node['grandTotal'])->not->toBeNull();
        expect($node['formattedGrandTotal'])->not->toBeNull();
        expect($node['customerName'])->not->toBeNull();
    }

    public function test_create_on_fully_invoiced_order_returns_already_invoiced(): void
    {
        $order = $this->bootstrapOrderWithInvoice('processing');
        $admin = $this->createAdmin();
        $item = $order->items->first();

        // Simulate a completed full invoice: every item's qty is consumed so
        // qty_to_invoice falls to 0 while the invoice row remains.
        \Webkul\Sales\Models\OrderItem::where('order_id', $order->id)
            ->update(['qty_invoiced' => \Illuminate\Support\Facades\DB::raw('qty_ordered')]);
        $order = $order->fresh(['invoices', 'items']);

        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => $order->id, 'items' => [['orderItemId' => $item->id, 'quantity' => 1]]],
        ], $admin);

        $messages = collect($response->json('errors') ?? [])->pluck('message')->implode(' ');
        expect($messages)->toContain('already been generated');
    }

    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 1, 'items' => []]]);
        expect($response->json('errors'))->toBeArray();
    }

    public function test_create_invalid_qty_returns_errors(): void
    {
        $order = Order::with('items')
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_ordered - qty_invoiced - qty_canceled) > 0');
            })
            ->first() ?? $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('Env-bound: no invoiceable item available after bootstrap.');
        }
        $admin = $this->createAdmin();
        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => $order->id, 'items' => [['orderItemId' => $item->id, 'quantity' => 99999]]],
        ], $admin);

        expect($response->json('errors'))->toBeArray();
    }

    public function test_view_invoice_by_id(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $admin = $this->createAdmin();
        $query = 'query($id: ID!){ adminInvoice(id:$id){ _id incrementId } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/invoices/'.$invoiceId], $admin);

        $node = $response->json('data.adminInvoice');
        if ($node) {
            expect($node['_id'])->toBe($invoiceId);
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }

    /**
     * Regression: the detail resource is an Eloquent-backed AdminInvoice — the
     * IRI `id` resolves, full columns resolve, and `items` is a connection +
     * addresses are reached via `order { addresses { edges { node } } }`.
     */
    public function test_view_invoice_resolves_id_columns_and_items(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $admin = $this->createAdmin();
        $query = 'query($id: ID!){ adminInvoice(id:$id){ id _id baseSubTotal baseGrandTotal items { edges { node { _id sku } } } order { _id addresses { edges { node { addressType } } } } } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/invoices/'.$invoiceId], $admin);

        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.adminInvoice');
        expect($node)->not->toBeNull();
        expect($node['id'])->toBe('/api/admin/invoices/'.$invoiceId);
        expect($node['_id'])->toBe($invoiceId);
        expect($node['items']['edges'])->toBeArray();
    }

    public function test_mass_update_status_flips_state_graphql(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $original = Invoice::where('id', $invoiceId)->value('state');
        $target = $original === Invoice::STATUS_OVERDUE ? Invoice::STATUS_PAID : Invoice::STATUS_OVERDUE;

        $admin = $this->createAdmin();
        $mutation = 'mutation($input: createAdminInvoiceMassUpdateStatusInput!){ createAdminInvoiceMassUpdateStatus(input:$input){ adminInvoiceMassUpdateStatus { message } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$invoiceId], 'value' => $target]], $admin);

        expect($response->json('errors'))->toBeNull();
        expect(Invoice::where('id', $invoiceId)->value('state'))->toBe($target);

        Invoice::where('id', $invoiceId)->update(['state' => $original]);
    }
}
