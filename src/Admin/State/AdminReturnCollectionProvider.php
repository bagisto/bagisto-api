<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminReturn;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Support\Sql;

class AdminReturnCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    /**
     * The permission constant.
     */
    protected const PERMISSION = 'sales.rma.requests';

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'order_id', 'created_at'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        return DB::table('rma')
            ->leftJoin('orders', 'orders.id', '=', 'rma.order_id')
            ->leftJoin('rma_statuses', 'rma_statuses.id', '=', 'rma.rma_status_id')
            ->select(
                'rma.id',
                'rma.order_id',
                'orders.increment_id as order_increment_id',
                'orders.status as order_status',
                'orders.is_guest',
                'orders.customer_email',
                DB::raw(Sql::fullName('orders.customer_first_name', 'orders.customer_last_name').' as customer_name'),
                'rma.rma_status_id as status_id',
                'rma_statuses.title as status_title',
                'rma_statuses.color as status_color',
                'rma.created_at',
                'rma.updated_at',
            );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        $prefix = DB::getTablePrefix();

        if (! empty($args['id'])) {
            $query->where('rma.id', (int) $args['id']);
        }

        if (! empty($args['order_id'])) {
            $query->where('rma.order_id', (int) $args['order_id']);
        }

        if (! empty($args['status'])) {
            $query->where('rma_statuses.title', db_grammar()->caseInsensitiveLike(), '%'.$args['status'].'%');
        }

        if (! empty($args['customer_name'])) {
            $query->whereRaw(Sql::likeClause(Sql::fullName('orders.customer_first_name', 'orders.customer_last_name')), ['%'.$args['customer_name'].'%']);
        }

        if (! empty($args['created_at_from'])) {
            $query->whereDate('rma.created_at', '>=', $args['created_at_from']);
        }

        if (! empty($args['created_at_to'])) {
            $query->whereDate('rma.created_at', '<=', $args['created_at_to']);
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id' => 'rma.id',
            'order_id' => 'rma.order_id',
            'created_at' => 'rma.created_at',
        ];

        $query->orderBy($map[$column] ?? 'rma.id', $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminReturn
    {
        $dto = new AdminReturn;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->orderStatus = $row->order_status;
        $dto->isGuest = $row->is_guest !== null ? (int) $row->is_guest : null;
        $dto->customerName = trim((string) $row->customer_name) ?: null;
        $dto->customerEmail = $row->customer_email;
        $dto->statusId = $row->status_id !== null ? (int) $row->status_id : null;
        $dto->statusTitle = $row->status_title;
        $dto->statusColor = $row->status_color;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
