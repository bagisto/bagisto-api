<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;
use Webkul\Sales\Models\Refund;
use Webkul\Sales\Models\Shipment;

/**
 * Regression guard for the camelCase-over-GraphQL bug.
 *
 * The admin Sales output classes (the AdminInvoice resource, …) must declare
 * snake_case properties so multi-word camelCase fields resolve over
 * GraphQL — API Platform reads a GraphQL field value by snake-casing the field
 * name with no class context, so camelCase DTO properties come back null.
 *
 * Earlier admin GraphQL tests only ever queried single-token fields (`id`,
 * `state`), which always resolved, so this whole class of bug went unnoticed.
 * These tests deliberately query multi-word camelCase fields and assert they
 * are populated (not null).
 */
class SalesFieldResolutionTest extends AdminApiTestCase
{
    private function seedOrder(): Order
    {
        return Order::factory()->create(['increment_id' => 'ORD-FIELDRES-1']);
    }

    public function test_admin_invoice_detail_resolves_camel_case_fields(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();
        $invoice = Invoice::factory()->create([
            'order_id'         => $order->id,
            'increment_id'     => 'INV-FR',
            'state'            => 'paid',
            'grand_total'      => 250,
            'base_grand_total' => 199.50,
            'sub_total'        => 180,
            'created_at'       => '2026-01-10 10:00:00',
        ]);

        $query = 'query($id:ID!){ adminInvoice(id:$id){ incrementId orderId state grandTotal subTotal createdAt } }';
        $node = $this->adminGraphQL($query, ['id' => "/api/admin/invoices/{$invoice->id}"], $admin)
            ->json('data.adminInvoice');

        expect($node['incrementId'])->toBe('INV-FR');
        expect((int) $node['orderId'])->toBe($order->id);
        expect($node['state'])->toBe('paid');
        expect($node['grandTotal'])->not->toBeNull();
        expect($node['subTotal'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_admin_invoices_list_resolves_camel_case_fields(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();
        $invoice = Invoice::factory()->create([
            'order_id'            => $order->id,
            'increment_id'        => 'INV-FR-LIST',
            'state'               => 'paid',
            'grand_total'         => 250,
            'base_grand_total'    => 199.50,
            'order_currency_code' => 'USD',
            'created_at'          => '2026-01-10 10:00:00',
        ]);

        // The listing's GraphQL node shares the detail type, so BOTH grandTotal
        // (detail-named) and baseGrandTotal (list-named) must resolve + populate.
        $query = 'query{ adminInvoices(first:50){ edges{ node{ _id incrementId orderId orderIncrementId state grandTotal formattedGrandTotal baseGrandTotal formattedBaseGrandTotal createdAt } } } }';
        $edges = $this->adminGraphQL($query, [], $admin)->json('data.adminInvoices.edges');
        $node = collect($edges)->pluck('node')->firstWhere('_id', $invoice->id);

        expect($node)->not->toBeNull();
        expect($node['incrementId'])->toBe('INV-FR-LIST');
        expect((int) $node['orderId'])->toBe($order->id);
        expect($node['orderIncrementId'])->toBe('ORD-FIELDRES-1');
        expect($node['state'])->toBe('paid');
        expect((float) $node['grandTotal'])->toBe(250.0);
        expect($node['formattedGrandTotal'])->not->toBeNull();
        expect((float) $node['baseGrandTotal'])->toBe(199.50);
        expect($node['formattedBaseGrandTotal'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_admin_shipment_detail_resolves_camel_case_fields(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();
        $shipment = Shipment::factory()->create([
            'order_id'      => $order->id,
            'total_qty'     => 3,
            'carrier_title' => 'DHL',
            'created_at'    => '2026-01-11 10:00:00',
        ]);

        $query = 'query($id:ID!){ adminShipment(id:$id){ orderId totalQty carrierTitle createdAt } }';
        $node = $this->adminGraphQL($query, ['id' => "/api/admin/shipments/{$shipment->id}"], $admin)
            ->json('data.adminShipment');

        expect((int) $node['orderId'])->toBe($order->id);
        expect((int) $node['totalQty'])->toBe(3);
        expect($node['carrierTitle'])->toBe('DHL');
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_admin_refund_detail_resolves_camel_case_fields(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();
        $refund = Refund::factory()->create([
            'order_id'    => $order->id,
            'state'       => 'refunded',
            'grand_total' => 50,
            'sub_total'   => 40,
            'created_at'  => '2026-01-12 10:00:00',
        ]);

        $query = 'query($id:ID!){ adminRefund(id:$id){ orderId state grandTotal subTotal createdAt } }';
        $node = $this->adminGraphQL($query, ['id' => "/api/admin/refunds/{$refund->id}"], $admin)
            ->json('data.adminRefund');

        expect((int) $node['orderId'])->toBe($order->id);
        expect($node['state'])->toBe('refunded');
        expect($node['grandTotal'])->not->toBeNull();
        expect($node['subTotal'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_admin_transaction_resolves_camel_case_fields_on_list_and_detail(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();
        $txn = OrderTransaction::factory()->create([
            'order_id'       => $order->id,
            'transaction_id' => 'TXN-FR',
            'amount'         => 199.50,
            'status'         => 'paid',
            'type'           => 'order',
            'payment_method' => 'cashondelivery',
            'created_at'     => '2026-01-13 10:00:00',
        ]);

        // Detail
        $detail = $this->adminGraphQL(
            'query($id:ID!){ adminTransaction(id:$id){ transactionId orderId orderIncrementId amount status type paymentMethod createdAt } }',
            ['id' => "/api/admin/transactions/{$txn->id}"],
            $admin
        )->json('data.adminTransaction');

        expect($detail['transactionId'])->toBe('TXN-FR');
        expect((int) $detail['orderId'])->toBe($order->id);
        expect($detail['orderIncrementId'])->toBe('ORD-FIELDRES-1');
        expect($detail['paymentMethod'])->toBe('cashondelivery');
        expect($detail['createdAt'])->not->toBeNull();

        // List (transactions: list ⊆ detail, so the full field set resolves)
        $edges = $this->adminGraphQL(
            'query{ adminTransactions(first:50){ edges{ node{ _id transactionId orderId orderIncrementId paymentMethod createdAt } } } }',
            [],
            $admin
        )->json('data.adminTransactions.edges');
        $node = collect($edges)->pluck('node')->firstWhere('_id', $txn->id);

        expect($node)->not->toBeNull();
        expect($node['transactionId'])->toBe('TXN-FR');
        expect($node['orderIncrementId'])->toBe('ORD-FIELDRES-1');
        expect($node['paymentMethod'])->toBe('cashondelivery');
    }
}
