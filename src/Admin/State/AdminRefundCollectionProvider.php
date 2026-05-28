<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminRefundListDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Sales\Models\OrderAddress;

/**
 * GET /api/admin/refunds + adminRefunds cursor query.
 *
 * Mirrors OrderRefundDataGrid. Filters: id (exact/list), order_id (partial),
 * state, base_grand_total (exact or range), billed_to, created_at (range/preset).
 */
class AdminRefundCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.refunds.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'order_id', 'state', 'base_grand_total', 'billed_to', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        return DB::table('refunds')
            ->leftJoin('orders', 'refunds.order_id', '=', 'orders.id')
            ->leftJoin('addresses as order_address_billing', function ($join) {
                $join->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->select(
                'refunds.id as id',
                'refunds.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'refunds.state as state',
                'refunds.base_grand_total as base_grand_total',
                'refunds.created_at as created_at',
            )
            ->addSelect(DB::raw('CONCAT('.$prefix.'order_address_billing.first_name, " ", '.$prefix.'order_address_billing.last_name) as billed_to'));
    }

    protected function applyFilters($query, array $args): void
    {
        $prefix = DB::getTablePrefix();

        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('refunds.id', $ids);
            }
        }

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }

        if (! empty($args['state'])) {
            $query->where('refunds.state', $args['state']);
        }

        if (isset($args['base_grand_total']) && $args['base_grand_total'] !== '') {
            $query->where('refunds.base_grand_total', $args['base_grand_total']);
        }
        if (isset($args['base_grand_total_from']) && $args['base_grand_total_from'] !== '') {
            $query->where('refunds.base_grand_total', '>=', (float) $args['base_grand_total_from']);
        }
        if (isset($args['base_grand_total_to']) && $args['base_grand_total_to'] !== '') {
            $query->where('refunds.base_grand_total', '<=', (float) $args['base_grand_total_to']);
        }

        if (! empty($args['billed_to'])) {
            $query->whereRaw('CONCAT('.$prefix.'order_address_billing.first_name, " ", '.$prefix.'order_address_billing.last_name) like ?', ['%'.$args['billed_to'].'%']);
        }

        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('refunds.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('refunds.created_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    protected function applySort($query, array $args): void
    {
        [$col, $dir] = $this->resolveSort($args);

        $map = [
            'id'               => 'refunds.id',
            'order_id'         => 'orders.increment_id',
            'state'            => 'refunds.state',
            'base_grand_total' => 'refunds.base_grand_total',
            'billed_to'        => 'billed_to',
            'created_at'       => 'refunds.created_at',
        ];

        $query->orderBy($map[$col] ?? 'refunds.id', $dir);
    }

    protected function mapRow(object $row): AdminRefundListDto
    {
        $dto = new AdminRefundListDto;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->state = $row->state;
        $dto->baseGrandTotal = $row->base_grand_total !== null ? (float) $row->base_grand_total : null;
        $dto->formattedBaseGrandTotal = $row->base_grand_total !== null
            ? core()->formatBasePrice((float) $row->base_grand_total)
            : null;
        $dto->billedTo = trim((string) $row->billed_to) ?: null;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;

        return $dto;
    }
}
