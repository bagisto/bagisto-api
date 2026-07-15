<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Repositories\ShipmentRepository;

class AdminTransactionCreateProcessor implements ProcessorInterface
{
    use BuildsAdminTransaction;
    use ChecksAdminPermission;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected ShipmentRepository $shipmentRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminTransaction
    {
        $this->authorizedAdmin('sales.transactions.view', 'bagistoapi::app.admin.sales.transaction.no-permission');

        $invoiceId = $this->input($context, 'invoiceId', 'invoice_id');
        $paymentMethod = $this->input($context, 'paymentMethod', 'payment_method');
        $amount = $this->input($context, 'amount', 'amount');

        if (empty($invoiceId)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.invoice-required'), 422);
        }

        if (empty($paymentMethod)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.payment-method-required'), 422);
        }

        if ($amount === null || $amount === '' || ! is_numeric($amount)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.amount-invalid'), 422);
        }

        $amount = (float) $amount;

        $invoice = $this->invoiceRepository->find((int) $invoiceId);

        if (! $invoice) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.invoice-missing'), 400);
        }

        if ($invoice->state == 'paid') {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.already-paid'), 400);
        }

        $transactionAmtBefore = (float) $this->orderTransactionRepository->where('invoice_id', $invoice->id)->sum('amount');

        if ($amount + $transactionAmtBefore > (float) $invoice->base_grand_total) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.amount-exceeds'), 400);
        }

        if ($amount <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.transaction.create.amount-zero'), 400);
        }

        $order = $this->orderRepository->find($invoice->order_id);

        $transaction = $this->orderTransactionRepository->create([
            'transaction_id' => bin2hex(random_bytes(20)),
            'type' => $paymentMethod,
            'payment_method' => $paymentMethod,
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id,
            'amount' => $amount,
            'status' => 'paid',
            'data' => json_encode([
                'paidAmount' => $amount,
            ]),
        ]);

        $transactionTotal = (float) $this->orderTransactionRepository->where('invoice_id', $invoice->id)->sum('amount');

        if ($transactionTotal >= (float) $invoice->base_grand_total) {
            $shipment = $this->shipmentRepository->where('order_id', $invoice->order_id)->first();

            $this->orderRepository->updateOrderStatus(
                $order,
                $shipment ? Order::STATUS_COMPLETED : Order::STATUS_PROCESSING
            );

            $this->invoiceRepository->updateState($invoice, Invoice::STATUS_PAID);
        }

        return $this->loadTransaction((int) $transaction->id);
    }

    protected function loadTransaction(int $id): AdminTransaction
    {
        $row = DB::table('order_transactions')
            ->leftJoin('orders', 'order_transactions.order_id', '=', 'orders.id')
            ->select($this->adminTransactionSelect())
            ->where('order_transactions.id', $id)
            ->first();

        return $this->buildAdminTransaction($row);
    }

    protected function input(array $context, string $camel, string $snake): mixed
    {
        return $context['args']['input'][$camel]
            ?? request()->input($camel)
            ?? request()->input($snake);
    }
}
