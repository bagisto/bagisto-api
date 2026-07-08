<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminTransaction;

trait BuildsAdminTransaction
{
    protected function buildAdminTransaction(object $row): AdminTransaction
    {
        $txn = new AdminTransaction;

        $txn->id = (int) $row->id;
        $txn->transactionId = $row->transaction_id ?? null;
        $txn->invoiceId = $row->invoice_id !== null ? (int) $row->invoice_id : null;
        $txn->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $txn->orderIncrementId = $row->order_increment_id ?? null;
        $txn->amount = $row->amount !== null ? (float) $row->amount : null;
        $txn->formattedAmount = $row->amount !== null ? $this->safeFormatBaseAmount((float) $row->amount) : null;
        $txn->status = $row->status ?? null;
        $txn->type = $row->type ?? null;
        $txn->paymentMethod = $row->payment_method ?? null;
        $txn->paymentTitle = $row->payment_method
            ? core()->getConfigData('sales.payment_methods.'.$row->payment_method.'.title')
            : null;

        $data = $row->data_json ?? null;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : null;
        }
        $txn->data = is_array($data) ? $data : null;

        $txn->createdAt = $row->created_at ? (string) $row->created_at : null;
        $txn->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        if ($row->order_id) {
            $name = trim(($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));

            $txn->order = [
                'id' => (int) $row->order_id,
                'incrementId' => $row->order_increment_id ?? null,
                'status' => $row->order_status ?? null,
                'customerName' => $name !== '' ? $name : null,
                'customerEmail' => $row->order_customer_email ?? null,
                'grandTotal' => isset($row->order_grand_total) && $row->order_grand_total !== null ? (float) $row->order_grand_total : null,
                'orderCurrencyCode' => $row->order_currency_code ?? null,
            ];
        }

        return $txn;
    }

    protected function adminTransactionSelect(): array
    {
        return [
            'order_transactions.id as id',
            'order_transactions.transaction_id as transaction_id',
            'order_transactions.invoice_id as invoice_id',
            'order_transactions.order_id as order_id',
            'order_transactions.amount as amount',
            'order_transactions.status as status',
            'order_transactions.type as type',
            'order_transactions.payment_method as payment_method',
            'order_transactions.data as data_json',
            'order_transactions.created_at as created_at',
            'order_transactions.updated_at as updated_at',
            'orders.increment_id as order_increment_id',
            'orders.status as order_status',
            'orders.grand_total as order_grand_total',
            'orders.order_currency_code as order_currency_code',
            'orders.customer_email as order_customer_email',
            'orders.customer_first_name as order_customer_first_name',
            'orders.customer_last_name as order_customer_last_name',
        ];
    }

    protected function safeFormatBaseAmount(float $amount): ?string
    {
        try {
            return core()->formatBasePrice($amount);
        } catch (\Throwable) {
            return (string) $amount;
        }
    }
}
