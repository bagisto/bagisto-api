<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminTransaction;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminTransactionCollectionProvider extends AbstractAdminCollectionProvider
{
    use BuildsAdminTransaction;
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
            ->select($this->adminTransactionSelect());
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

    protected function mapRow(object $row): AdminTransaction
    {
        return $this->buildAdminTransaction($row);
    }
}
