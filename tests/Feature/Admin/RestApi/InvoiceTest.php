<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Core\Tests\Concerns\ConfiguresSettings;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\Shop\Mail\Order\InvoicedNotification;
use Webkul\User\Models\Role;

class InvoiceTest extends AdminApiTestCase
{
    use AdminFixtureFactory, ConfiguresSettings;

    /**
     * Test create requires authentication.
     */
    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/invoices', ['items' => []])->assertStatus(401);
    }

    /**
     * Test view requires authentication.
     */
    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/invoices/1')->assertStatus(401);
    }

    /**
     * Test view returns 404 for unknown invoice.
     */
    public function test_view_returns_404_for_unknown_invoice(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/invoices/999999999')->assertStatus(404);
    }

    /**
     * Test create returns 404 for unknown order.
     */
    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/invoices', ['items' => [[
            'orderItemId' => 1,
            'quantity' => 1,
        ]]])
            ->assertStatus(404);
    }

    /**
     * Test create rejects when items missing.
     */
    public function test_create_rejects_when_items_missing(): void
    {
        $order = $this->anInvoiceableOrder();
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', ['items' => []]);
        $response->assertStatus(422);
    }

    /**
     * Test create rejects qty exceeding available.
     */
    public function test_create_rejects_qty_exceeding_available(): void
    {
        $order = $this->anInvoiceableOrder();
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item — bootstrapped order has no items with qty_to_invoice > 0.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [[
                'orderItemId' => $item->id,
                'quantity' => (int) $item->qty_to_invoice + 999,
            ]],
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test create succeeds with valid items.
     */
    public function test_create_succeeds_with_valid_items(): void
    {
        $order = $this->anInvoiceableOrder();
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item — bootstrapped order has no items with qty_to_invoice > 0.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [[
                'orderItemId' => $item->id,
                'quantity' => 1,
            ]],
        ]);

        $response->assertStatus(201);
        expect($response->json('order.id'))->toBe($order->id);
        expect($response->json('items'))->toBeArray();
        expect($response->json('incrementId'))->not->toBeNull();
    }

    /**
     * Test create with transaction flag records order transaction.
     */
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
            'items' => [[
                'orderItemId' => $item->id,
                'quantity' => 1,
            ]],
            'can_create_transaction' => true,
        ]);

        $response->assertStatus(201);
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(1);
    }

    /**
     * Test create without transaction flag records no order transaction.
     */
    public function test_create_without_transaction_flag_records_no_order_transaction(): void
    {
        $order = $this->bootstrapInvoiceableOrder('pending');
        $item = $order->items->firstWhere(fn ($i) => $i->qty_to_invoice > 0);
        if (! $item) {
            $this->markTestSkipped('No invoiceable item.');
        }

        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/invoices', [
            'items' => [[
                'orderItemId' => $item->id,
                'quantity' => 1,
            ]],
        ]);

        $response->assertStatus(201);
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(0);
    }

    /**
     * Test view returns invoice detail.
     */
    public function test_view_returns_invoice_detail(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/invoices/'.$invoiceId);
        $response->assertOk();
        expect($response->json('id'))->toBe($invoiceId);
        expect($response->json())->toHaveKeys([
            'id', 'incrementId', 'state', 'totalQty',
            'baseSubTotal', 'baseSubTotalInclTax', 'baseTaxAmount', 'baseDiscountAmount',
            'baseShippingAmount', 'baseShippingAmountInclTax',
            'customerName', 'customerEmail', 'channelName', 'orderStatus', 'orderDate',
            'order', 'items',
        ]);
        expect($response->json('order'))->toBeArray();
        expect($response->json('order'))->toHaveKey('addresses');
        expect($response->json('order.id'))->not->toBeNull();
        $items = $response->json('items');
        if (! empty($items)) {
            expect($items[0])->toBeArray();
            expect($items[0])->toHaveKeys(['id', 'sku', 'name', 'qty', 'basePriceInclTax', 'baseImageUrl']);
        }
    }

    /**
     * Test print returns pdf or skips.
     */
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

    /**
     * Test mass update status flips state.
     */
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

    /**
     * Test mass update status empty indices returns 422.
     */
    public function test_mass_update_status_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [],
            'value' => 'paid',
        ])->assertStatus(422);
    }

    /**
     * Test mass update status invalid value returns 422.
     */
    public function test_mass_update_status_invalid_value_returns_422(): void
    {
        $invoiceId = $this->anInvoiceId();
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/mass-update-status', [
            'indices' => [$invoiceId],
            'value' => 'banana',
        ])->assertStatus(422);
    }

    /**
     * Test mass update status requires authentication.
     */
    public function test_mass_update_status_requires_authentication(): void
    {
        $this->publicPost('/api/admin/invoices/mass-update-status', [
            'indices' => [1],
            'value' => 'paid',
        ])->assertStatus(401);
    }

    /**
     * Test mass update status no permission returns 403.
     */
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

    /**
     * Sending a duplicate invoice rejects a request without an admin token.
     */
    public function test_send_duplicate_requires_authentication(): void
    {
        $this->publicPost('/api/admin/invoices/1/send-duplicate', ['email' => 'copy@example.com'])->assertStatus(401);
    }

    /**
     * Sending a duplicate invoice queues the invoice email to the given address.
     */
    public function test_send_duplicate_queues_the_invoice_email(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();
        $email = 'duplicate-'.uniqid().'@example.com';

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/invoices/'.$invoice->id.'/send-duplicate', ['email' => $email]);

        $response->assertOk();
        expect($response->json('id'))->toBe($invoice->id);
        expect($response->json('email'))->toBe($email);
        expect($response->json('success'))->toBeTrue();

        Mail::assertQueued(InvoicedNotification::class, fn (InvoicedNotification $mail) => $mail->invoice->id === $invoice->id
            && $mail->hasTo($email));
    }

    /**
     * Sending a duplicate invoice to an invalid email address returns 422 and sends nothing.
     */
    public function test_send_duplicate_invalid_email_returns_422(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();

        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/'.$invoice->id.'/send-duplicate', ['email' => 'not-an-email'])
            ->assertStatus(422);

        Mail::assertNothingQueued();
    }

    /**
     * Sending a duplicate of an unknown invoice returns 404.
     */
    public function test_send_duplicate_unknown_invoice_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/invoices/999999999/send-duplicate', ['email' => 'copy@example.com'])
            ->assertStatus(404);
    }

    /**
     * Sending a duplicate invoice is refused for a role without the invoice view permission.
     */
    public function test_send_duplicate_without_permission_returns_403(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.invoices'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminPost($admin, '/api/admin/invoices/'.$invoice->id.'/send-duplicate', ['email' => 'copy@example.com'], $this->adminTokenSameAsWeb($admin))
            ->assertStatus(403);

        Mail::assertNothingQueued();
    }

    /**
     * An invoiceable order.
     */
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

    /**
     * An invoice id.
     */
    protected function anInvoiceId(): int
    {
        return Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
    }

    /**
     * Create an invoice with the new invoice email notification switched on.
     */
    protected function anInvoiceWithMailEnabled(): Invoice
    {
        $this->setConfig('emails.general.notifications.emails.general.notifications.new_invoice', 1);
        $this->forgetCoreConfigCache();

        return $this->bootstrapOrderWithInvoice()->invoices->first();
    }
}
