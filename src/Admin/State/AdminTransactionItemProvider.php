<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Dto\AdminTransactionDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;

/**
 * GET /api/admin/transactions/{id} + adminTransaction(id:) query.
 *
 * Returns the full transaction row + slim summary of the linked order.
 */
class AdminTransactionItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.transactions.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.sales.transaction.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        // `OrderTransaction` model does NOT declare an `order()` relation in
        // core (Bagisto 2.3.x), so eager-loading it would throw
        // "Call to undefined relationship [order]". Resolve the order
        // manually inside `mapToDto()` via `Order::find($order_id)`.
        return OrderTransaction::find($id);
    }

    protected function mapToDto(object $entity): AdminTransactionDetailDto
    {
        /** @var \Webkul\Sales\Models\OrderTransaction $entity */
        $dto = new AdminTransactionDetailDto;
        $dto->id = (int) $entity->id;
        $dto->transactionId = $entity->transaction_id;
        $dto->invoiceId = $entity->invoice_id !== null ? (int) $entity->invoice_id : null;
        $dto->orderId = $entity->order_id !== null ? (int) $entity->order_id : null;

        $order = $entity->order_id !== null ? Order::find($entity->order_id) : null;

        $dto->orderIncrementId = $order?->increment_id;
        $dto->amount = $entity->amount !== null ? (float) $entity->amount : null;
        $dto->formattedAmount = $entity->amount !== null ? core()->formatBasePrice((float) $entity->amount) : null;
        $dto->status = $entity->status;
        $dto->type = $entity->type;
        $dto->paymentMethod = $entity->payment_method;

        try {
            $dto->paymentTitle = $entity->payment_title;
        } catch (\Throwable) {
            $dto->paymentTitle = null;
        }

        $data = $entity->data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : null;
        }
        $dto->data = is_array($data) ? $data : null;

        $dto->createdAt = $entity->created_at ? (string) $entity->created_at : null;
        $dto->updatedAt = $entity->updated_at ? (string) $entity->updated_at : null;

        if ($order) {
            $dto->order = [
                'id'                => (int) $order->id,
                'incrementId'       => $order->increment_id,
                'status'            => $order->status,
                'grandTotal'        => $order->grand_total !== null ? (float) $order->grand_total : null,
                'orderCurrencyCode' => $order->order_currency_code,
                'customerEmail'     => $order->customer_email,
            ];
        }

        return $dto;
    }
}
