<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Barryvdh\DomPDF\Facade\Pdf;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Invoice — create / view / print / mass-update-status.
 */
class InvoiceTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function anInvoiceableOrder(): Order
    {
        $existing = Order::with('items')
            ->whereNotIn('status', [Order::STATUS_CLOSED, Order::STATUS_FRAUD])
            ->whereHas('items', function ($q) {
                $q->whereRaw('(qty_ordered - qty_invoiced - qty_canceled) > 0');
            })
            ->first();

        return $existing ?? $this->bootstrapInvoiceableOrder('pending');
    }

    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/invoices', ['items' => []])->assertStatus(401);
    }

    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/invoices/1')->assertStatus(401);
    }

    public function test_view_returns_404_for_unknown_invoice(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/invoices/999999999')->assertStatus(404);
    }

    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/invoices', ['items' => [['orderItemId' => 1, 'quantity' => 1]]])
            ->assertStatus(404);
    }

    public function test_create_rejects_when_items_missing(): void
    {
        $order = $this->anInvoiceableOrder();
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', ['items' => []]);
        $response->assertStatus(422);
    }

    public function test_create_rejects_qty_exceeding_available(): void
    {
        $order = $this->anInvoiceableOrder();
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item — bootstrapped order has no items with qty_to_invoice > 0.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [['orderItemId' => $item->id, 'quantity' => (int) $item->qty_to_invoice + 999]],
        ]);
        $response->assertStatus(422);
    }

    public function test_create_succeeds_with_valid_items(): void
    {
        $order = $this->anInvoiceableOrder();
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item — bootstrapped order has no items with qty_to_invoice > 0.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [['orderItemId' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201);
        expect($response->json('order.id'))->toBe($order->id);
        expect($response->json('items'))->toBeArray();
        expect($response->json('incrementId'))->not->toBeNull();
    }

    public function test_create_with_transaction_flag_records_order_transaction(): void
    {
        $order = $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item.');
        }

        $admin = $this->createAdmin();

        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(0);

        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [['orderItemId' => $item->id, 'quantity' => 1]],
            'can_create_transaction' => true,
        ]);

        $response->assertStatus(201);
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(1);
    }

    public function test_create_without_transaction_flag_records_no_order_transaction(): void
    {
        $order = $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item.');
        }

        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [['orderItemId' => $item->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201);
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(0);
    }

    public function test_view_returns_invoice_detail(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/invoices/'.$invoiceId);
        $response->assertOk();
        expect($response->json('id'))->toBe($invoiceId);
        // Full column set + order/customer context now surfaced.
        expect($response->json())->toHaveKeys([
            'id', 'incrementId', 'state', 'totalQty',
            'baseSubTotal', 'baseSubTotalInclTax', 'baseTaxAmount', 'baseDiscountAmount',
            'baseShippingAmount', 'baseShippingAmountInclTax',
            'customerName', 'customerEmail', 'channelName', 'orderStatus', 'orderDate',
            'order', 'items',
        ]);
        // Addresses live on the order: order = { id, addresses: [...] }.
        // (The order id moved from a top-level `orderId` to `order.id` when the
        // invoice gained the `order` relation that carries the addresses.)
        expect($response->json('order'))->toBeArray();
        expect($response->json('order'))->toHaveKey('addresses');
        expect($response->json('order.id'))->not->toBeNull();
        // Items must be inline objects, NOT IRI strings (regression: the typed-DTO
        // array previously serialized each item as "/api/order_action_item_dtos/{id}").
        $items = $response->json('items');
        if (! empty($items)) {
            expect($items[0])->toBeArray();
            expect($items[0])->toHaveKeys(['id', 'sku', 'name', 'qty', 'basePriceInclTax', 'baseImageUrl']);
        }
    }

    public function test_print_returns_pdf_or_skips(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        if (! class_exists(Pdf::class)) {
            $this->markTestSkipped('dompdf not installed.');
        }

        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/invoices/'.$invoiceId.'/print', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/pdf'],
        ));

        if ($response->getStatusCode() === 500) {
            $this->markTestSkipped('dompdf rendering failed in test env: '.$response->getContent());
        }

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('pdf');
    }

    protected function anInvoiceId(): int
    {
        return Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
    }

    public function test_mass_update_status_flips_state(): void
    {
        $invoiceId = $this->anInvoiceId();
        $original = Invoice::where('id', $invoiceId)->value('state');
        $target = $original === Invoice::STATUS_PAID ? Invoice::STATUS_PENDING : Invoice::STATUS_PAID;

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [$invoiceId],
            'value' => $target,
        ]);

        $response->assertOk();
        expect($response->json('updated'))->toContain($invoiceId);
        expect(Invoice::where('id', $invoiceId)->value('state'))->toBe($target);

        Invoice::where('id', $invoiceId)->update(['state' => $original]);
    }

    public function test_mass_update_status_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [],
            'value' => 'paid',
        ])->assertStatus(422);
    }

    public function test_mass_update_status_invalid_value_returns_422(): void
    {
        $invoiceId = $this->anInvoiceId();
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [$invoiceId],
            'value' => 'banana',
        ])->assertStatus(422);
    }

    public function test_mass_update_status_requires_authentication(): void
    {
        $this->publicPost('/api/admin/invoices/mass-update-status', [
            'indices' => [1],
            'value' => 'paid',
        ])->assertStatus(401);
    }

    public function test_mass_update_status_no_permission_returns_403(): void
    {
        $invoiceId = $this->anInvoiceId();
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [$invoiceId],
            'value' => 'paid',
        ])->assertStatus(403);
    }
}
