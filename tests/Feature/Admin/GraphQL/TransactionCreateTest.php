<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Invoice;

class TransactionCreateTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function anUnpaidInvoice(): Invoice
    {
        $order = $this->bootstrapInvoiceableOrder('processing');
        $item = $order->items->first();

        return Invoice::factory()->create([
            'order_id' => $order->id,
            'state' => 'pending',
            'total_qty' => (int) $item->qty_ordered,
            'sub_total' => 100,
            'base_sub_total' => 100,
            'grand_total' => 100,
            'base_grand_total' => 100,
            'base_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'channel_currency_code' => 'USD',
            'increment_id' => 'INV-GTX-'.uniqid(),
        ]);
    }

    protected function mutation(): string
    {
        return <<<'GQL'
            mutation Create($input: createAdminTransactionInput!) {
              createAdminTransaction(input: $input) {
                adminTransaction {
                  _id
                  transactionId
                  invoiceId
                  amount
                  status
                }
              }
            }
        GQL;
    }

    public function test_create_mutation_records_payment(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $response = $this->adminGraphQL($this->mutation(), [
            'input' => [
                'invoiceId' => $invoice->id,
                'paymentMethod' => 'cashondelivery',
                'amount' => 100,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $txn = $response->json('data.createAdminTransaction.adminTransaction');
        expect($txn)->not->toBeNull();
        expect($txn['invoiceId'])->toBe($invoice->id);
        expect((float) $txn['amount'])->toBe(100.0);
        expect($txn['status'])->toBe('paid');
        expect($txn['transactionId'])->not->toBeNull();

        expect(Invoice::find($invoice->id)->state)->toBe('paid');
    }

    public function test_create_mutation_rejects_overpayment(): void
    {
        $admin = $this->createAdmin();
        $invoice = $this->anUnpaidInvoice();

        $response = $this->adminGraphQL($this->mutation(), [
            'input' => [
                'invoiceId' => $invoice->id,
                'paymentMethod' => 'cashondelivery',
                'amount' => 200,
            ],
        ], $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect(Invoice::find($invoice->id)->state)->not->toBe('paid');
    }

    public function test_create_mutation_requires_authentication(): void
    {
        $invoice = $this->anUnpaidInvoice();

        $response = $this->adminGraphQL($this->mutation(), [
            'input' => [
                'invoiceId' => $invoice->id,
                'paymentMethod' => 'cashondelivery',
                'amount' => 100,
            ],
        ]);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
    }
}
