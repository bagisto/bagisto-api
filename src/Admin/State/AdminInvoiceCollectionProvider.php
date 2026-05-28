<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceListDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

/**
 * GET /api/admin/invoices + adminInvoices cursor query.
 *
 * DataGrid parity with Webkul\Admin\DataGrids\Sales\OrderInvoiceDataGrid.
 * Filters: id (exact/list), order_id (partial on increment_id), state,
 * base_grand_total (exact or range via base_grand_total_from/to), and
 * created_at (range / preset). Sort: id (default desc), increment_id,
 * order_id, base_grand_total, state, created_at.
 */
class AdminInvoiceCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.invoices.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'increment_id', 'order_id', 'base_grand_total', 'state', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        return DB::table('invoices')
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->select(
                'invoices.id as id',
                'invoices.increment_id as increment_id',
                'invoices.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'invoices.state as state',
                'invoices.base_grand_total as base_grand_total',
                'invoices.created_at as created_at',
            )
            ->selectRaw("CASE WHEN {$prefix}invoices.increment_id IS NOT NULL THEN {$prefix}invoices.increment_id ELSE {$prefix}invoices.id END AS resolved_increment_id");
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('invoices.id', $ids);
            }
        }

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }

        if (! empty($args['state'])) {
            $query->where('invoices.state', $args['state']);
        }

        if (isset($args['base_grand_total']) && $args['base_grand_total'] !== '') {
            $query->where('invoices.base_grand_total', $args['base_grand_total']);
        }
        if (isset($args['base_grand_total_from']) && $args['base_grand_total_from'] !== '') {
            $query->where('invoices.base_grand_total', '>=', (float) $args['base_grand_total_from']);
        }
        if (isset($args['base_grand_total_to']) && $args['base_grand_total_to'] !== '') {
            $query->where('invoices.base_grand_total', '<=', (float) $args['base_grand_total_to']);
        }

        [$from, $to] = $this->resolveDateRange($args);
        if ($from) {
            $query->where('invoices.created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $query->where('invoices.created_at', '<=', $to->endOfDay());
        }
    }

    protected function applySort($query, array $args): void
    {
        [$col, $dir] = $this->resolveSort($args);

        if ($col === 'order_id') {
            $query->orderBy('orders.increment_id', $dir);
        } else {
            $query->orderBy('invoices.'.$col, $dir);
        }
    }

    protected function mapRow(object $row): AdminInvoiceListDto
    {
        $dto = new AdminInvoiceListDto;
        $dto->id = (int) $row->id;
        $dto->incrementId = $row->resolved_increment_id ?? $row->increment_id ?? (string) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->state = $row->state;
        $dto->baseGrandTotal = $row->base_grand_total !== null ? (float) $row->base_grand_total : null;
        $dto->formattedBaseGrandTotal = $row->base_grand_total !== null
            ? core()->formatBasePrice((float) $row->base_grand_total)
            : null;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;

        return $dto;
    }

    /**
     * Resolve date range from explicit from/to or `date_range` preset.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveDateRange(array $args): array
    {
        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;

        if ($from || $to) {
            return [
                $from ? Carbon::parse($from) : null,
                $to ? Carbon::parse($to) : null,
            ];
        }

        $now = Carbon::now();

        return match ($args['date_range'] ?? null) {
            'today'         => [$now->copy(), $now->copy()],
            'yesterday'     => [$now->copy()->subDay(), $now->copy()->subDay()],
            'this_week'     => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month'    => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month'    => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$now->copy()->subMonthsNoOverflow(3), $now->copy()],
            'last_6_months' => [$now->copy()->subMonthsNoOverflow(6), $now->copy()],
            'this_year'     => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default         => [null, null],
        };
    }
}
