<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminTransactionListDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

/**
 * GET /api/admin/transactions + adminTransactions cursor query.
 *
 * Mirrors OrderTransactionDataGrid. Filters: id, transaction_id, invoice_id,
 * order_id (partial on increment_id), status (dropdown), created_at (range).
 */
class AdminTransactionCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.transactions.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'transaction_id', 'amount', 'invoice_id', 'order_id', 'status', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('order_transactions')
            ->leftJoin('orders', 'order_transactions.order_id', '=', 'orders.id')
            ->select(
                'order_transactions.id as id',
                'order_transactions.transaction_id as transaction_id',
                'order_transactions.invoice_id as invoice_id',
                'order_transactions.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'order_transactions.created_at as created_at',
                'order_transactions.amount as amount',
                'order_transactions.status as status',
                'order_transactions.type as type',
                'order_transactions.payment_method as payment_method',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('order_transactions.id', $ids);
            }
        }

        if (! empty($args['transaction_id'])) {
            $query->where('order_transactions.transaction_id', 'like', '%'.$args['transaction_id'].'%');
        }

        if (! empty($args['invoice_id'])) {
            $query->where('order_transactions.invoice_id', $args['invoice_id']);
        }

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }

        if (! empty($args['status'])) {
            $query->where('order_transactions.status', $args['status']);
        }

        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('order_transactions.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('order_transactions.created_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    protected function applySort($query, array $args): void
    {
        [$col, $dir] = $this->resolveSort($args);

        if ($col === 'order_id') {
            $query->orderBy('orders.increment_id', $dir);
        } else {
            $query->orderBy('order_transactions.'.$col, $dir);
        }
    }

    protected function mapRow(object $row): AdminTransactionListDto
    {
        $dto = new AdminTransactionListDto;
        $dto->id = (int) $row->id;
        $dto->transactionId = $row->transaction_id;
        $dto->invoiceId = $row->invoice_id !== null ? (int) $row->invoice_id : null;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->amount = $row->amount !== null ? (float) $row->amount : null;
        $dto->formattedAmount = $row->amount !== null ? core()->formatBasePrice((float) $row->amount) : null;
        $dto->status = $row->status;
        $dto->type = $row->type;
        $dto->paymentMethod = $row->payment_method;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;

        return $dto;
    }
}
