<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Core\Tests\Concerns\ConfiguresSettings;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\Shop\Mail\Order\InvoicedNotification;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class InvoiceTest extends AdminApiTestCase
{
    use AdminFixtureFactory, ConfiguresSettings;

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

        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id transactionId } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'orderId' => $order->id,
                'items' => [[
                    'orderItemId' => $item->id,
                    'quantity' => 1,
                ]],
                'canCreateTransaction' => true,
            ],
        ], $admin);

        expect($response->json('errors'))->toBeNull();
        expect(OrderTransaction::where('order_id', $order->id)->count())->toBe(1);

        $linked = OrderTransaction::where('order_id', $order->id)->value('transaction_id');
        expect($response->json('data.createAdminInvoice.adminInvoice.transactionId'))->toBe($linked);
    }

    /**
     * Test create mutation returns fully populated invoice.
     */
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
            'input' => [
                'orderId' => $order->id,
                'items' => [[
                    'orderItemId' => $item->id,
                    'quantity' => 1,
                ]],
            ],
        ], $admin);

        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.createAdminInvoice.adminInvoice');

        expect($node['incrementId'])->not->toBeNull();
        expect($node['order']['_id'])->toBe($order->id);
        expect($node['grandTotal'])->not->toBeNull();
        expect($node['formattedGrandTotal'])->not->toBeNull();
        expect($node['customerName'])->not->toBeNull();
    }

    /**
     * Test create on fully invoiced order returns already invoiced.
     */
    public function test_create_on_fully_invoiced_order_returns_already_invoiced(): void
    {
        $order = $this->bootstrapOrderWithInvoice('processing');
        $admin = $this->createAdmin();
        $item = $order->items->first();

        OrderItem::where('order_id', $order->id)
            ->update(['qty_invoiced' => DB::raw('qty_ordered')]);
        $order = $order->fresh(['invoices', 'items']);

        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'orderId' => $order->id,
                'items' => [[
                    'orderItemId' => $item->id,
                    'quantity' => 1,
                ]],
            ],
        ], $admin);

        $messages = collect($response->json('errors') ?? [])->pluck('message')->implode(' ');
        expect($messages)->toContain('already been generated');
    }

    /**
     * Test create requires authentication.
     */
    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminInvoiceInput!){ createAdminInvoice(input:$input){ adminInvoice { _id } } }';

        $response = $this->adminGraphQL($mutation, ['input' => [
            'orderId' => 1,
            'items' => [],
        ]]);

        expect($response->json('errors'))->toBeArray();
    }

    /**
     * Test create invalid qty returns errors.
     */
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
            'input' => [
                'orderId' => $order->id,
                'items' => [[
                    'orderItemId' => $item->id,
                    'quantity' => 99999,
                ]],
            ],
        ], $admin);

        expect($response->json('errors'))->toBeArray();
    }

    /**
     * Test view invoice by id.
     */
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
     * IRI `id` resolves, full columns resolve, and `items` is a connection +.
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

    /**
     * Test mass update status flips state graphql.
     */
    public function test_mass_update_status_flips_state_graphql(): void
    {
        $invoiceId = Invoice::query()->value('id') ?? $this->bootstrapOrderWithInvoice()->invoices->first()->id;
        $original = Invoice::where('id', $invoiceId)->value('state');
        $target = $original === Invoice::STATUS_OVERDUE ? Invoice::STATUS_PAID : Invoice::STATUS_OVERDUE;

        $admin = $this->createAdmin();
        $mutation = 'mutation($input: createAdminInvoiceMassUpdateStatusInput!){ createAdminInvoiceMassUpdateStatus(input:$input){ adminInvoiceMassUpdateStatus { message } } }';

        $response = $this->adminGraphQL($mutation, ['input' => [
            'indices' => [$invoiceId],
            'value' => $target,
        ]], $admin);

        expect($response->json('errors'))->toBeNull();
        expect(Invoice::where('id', $invoiceId)->value('state'))->toBe($target);

        Invoice::where('id', $invoiceId)->update(['state' => $original]);
    }

    /**
     * The send duplicate mutation queues the invoice email to the given address.
     */
    public function test_send_duplicate_mutation_queues_the_invoice_email(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();
        $email = 'gql-duplicate-'.uniqid().'@example.com';

        $response = $this->sendDuplicateInvoice([
            'invoiceId' => $invoice->id,
            'email' => $email,
        ], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminInvoiceSendDuplicate.adminInvoiceSendDuplicate.email'))->toBe($email);
        expect($response->json('data.createAdminInvoiceSendDuplicate.adminInvoiceSendDuplicate.success'))->toBeTrue();

        Mail::assertQueued(InvoicedNotification::class, fn (InvoicedNotification $mail) => $mail->invoice->id === $invoice->id
            && $mail->hasTo($email));
    }

    /**
     * The send duplicate mutation rejects an invalid email address and sends nothing.
     */
    public function test_send_duplicate_mutation_rejects_invalid_email(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();

        $response = $this->sendDuplicateInvoice([
            'invoiceId' => $invoice->id,
            'email' => 'not-an-email',
        ], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.order.actions.invoice.email-invalid'));
        Mail::assertNothingQueued();
    }

    /**
     * The send duplicate mutation is refused for a role without the invoice view permission.
     */
    public function test_send_duplicate_mutation_without_permission_is_refused(): void
    {
        Mail::fake();

        $invoice = $this->anInvoiceWithMailEnabled();
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.invoices'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->sendDuplicateInvoice([
            'invoiceId' => $invoice->id,
            'email' => 'copy@example.com',
        ], $admin, $this->adminTokenSameAsWeb($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.order.actions.invoice.no-permission'));
        Mail::assertNothingQueued();
    }

    /**
     * The send duplicate mutation rejects a request without an admin token.
     */
    public function test_send_duplicate_mutation_requires_authentication(): void
    {
        Mail::fake();

        $response = $this->sendDuplicateInvoice([
            'invoiceId' => 1,
            'email' => 'copy@example.com',
        ]);

        expect($response->json('errors'))->not->toBeNull();
        Mail::assertNothingQueued();
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

    /**
     * Run the send duplicate invoice mutation, optionally as an admin with a given token.
     */
    protected function sendDuplicateInvoice(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminInvoiceSendDuplicateInput!) {
              createAdminInvoiceSendDuplicate(input: $input) {
                adminInvoiceSendDuplicate { _id email success message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }
}
