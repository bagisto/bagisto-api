<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Invoice;
use Webkul\User\Models\Role;

class TransactionCreateTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function anUnpaidInvoice(): Invoice
    {
        $order = $this->bootstrapInvoiceableOrder('processing');
        $item = $order->items->first();

        return Invoice::factory()->create([
            'order_id'              => $order->id,
            'state'                 => 'pending',
            'total_qty'             => (int) $item->qty_ordered,
            'sub_total'             => 100,
            'base_sub_total'        => 100,
            'grand_total'           => 100,
            'base_grand_total'      => 100,
            'base_currency_code'    => 'USD',
            'order_currency_code'   => 'USD',
            'channel_currency_code' => 'USD',
            'increment_id'          => 'INV-TX-'.uniqid(),
        ]);
    }

    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/transactions', [
            'invoiceId'     => 1,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 10,
        ])->assertStatus(401);
    }

    public function test_create_records_payment_and_marks_invoice_paid(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $response = $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 100,
        ]);

        $response->assertStatus(201);
        expect($response->json('invoiceId'))->toBe($invoice->id);
        expect((float) $response->json('amount'))->toBe(100.0);
        expect($response->json('status'))->toBe('paid');
        expect($response->json('transactionId'))->not->toBeNull();

        expect(Invoice::find($invoice->id)->state)->toBe('paid');
        $this->assertDatabaseHas('order_transactions', [
            'invoice_id' => $invoice->id,
            'amount'     => 100,
            'status'     => 'paid',
        ]);
    }

    public function test_partial_payment_keeps_invoice_unpaid(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 40,
        ])->assertStatus(201);

        expect(Invoice::find($invoice->id)->state)->not->toBe('paid');
    }

    public function test_overpayment_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 200,
        ])->assertStatus(400);
    }

    public function test_already_paid_invoice_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();
        $invoice->update(['state' => 'paid']);

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 10,
        ])->assertStatus(400);
    }

    public function test_zero_amount_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 0,
        ])->assertStatus(400);
    }

    public function test_unknown_invoice_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => 999999,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 10,
        ])->assertStatus(400);
    }

    public function test_missing_amount_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
        ])->assertStatus(422);
    }

    public function test_no_permission_returns_403(): void
    {
        $invoice = $this->anUnpaidInvoice();
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminPost($admin, '/api/admin/transactions', [
            'invoiceId'     => $invoice->id,
            'paymentMethod' => 'cashondelivery',
            'amount'        => 100,
        ])->assertStatus(403);
    }
}
