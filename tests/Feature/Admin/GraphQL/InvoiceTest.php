<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;

class InvoiceTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

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
     * Regression: the detail resource now returns AdminInvoice itself (not a
     * separate output DTO), so selecting the IRI `id` field no longer 500s the
     * whole query, and full columns + items inline resolve over GraphQL.
     */
    public function test_view_invoice_resolves_id_columns_and_items(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $admin = $this->createAdmin();
        $query = 'query($id: ID!){ adminInvoice(id:$id){ id _id baseSubTotal baseGrandTotal items } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/invoices/'.$invoiceId], $admin);

        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.adminInvoice');
        expect($node)->not->toBeNull();
        expect($node['id'])->toBe('/api/admin/invoices/'.$invoiceId);
        expect($node['_id'])->toBe($invoiceId);
        expect($node['items'])->toBeArray();
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
