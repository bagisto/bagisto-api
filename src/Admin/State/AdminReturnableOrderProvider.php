<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Webkul\Admin\DataGrids\Sales\RMA\OrderRMADataGrid;
use Webkul\BagistoApi\Admin\Models\AdminReturnableOrder;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Support\Sql;

class AdminReturnableOrderProvider extends AbstractAdminCollectionProvider
{
    /**
     * The sort columns, keyed by the name a client sends.
     */
    protected const SORT_COLUMNS = [
        'id' => 'orders.id',
        'increment_id' => 'orders.increment_id',
        'customer_name' => 'customer_name',
        'created_at' => 'orders.created_at',
        'grand_total' => 'orders.grand_total',
        'method_title' => 'order_payment.method_title',
        'status' => 'orders.status',
    ];

    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return array_keys(self::SORT_COLUMNS);
    }

    /**
     * Start from core's OrderRMADataGrid query, so eligibility matches the admin create form.
     */
    protected function buildQuery(array $args)
    {
        return datagrid(OrderRMADataGrid::class)->prepareQueryBuilder();
    }

    /**
     * Apply the filters the OrderRMADataGrid columns offer.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $query->where('orders.id', (int) $args['id']);
        }

        if (! empty($args['increment_id'])) {
            $query->where('orders.increment_id', db_grammar()->caseInsensitiveLike(), '%'.$args['increment_id'].'%');
        }

        if (! empty($args['customer_name'])) {
            $query->whereRaw(Sql::likeClause(Sql::fullName('orders.customer_first_name', 'orders.customer_last_name')), ['%'.$args['customer_name'].'%']);
        }

        if (! empty($args['status'])) {
            $query->where('orders.status', (string) $args['status']);
        }

        if (! empty($args['method_title'])) {
            $query->where('order_payment.method_title', (string) $args['method_title']);
        }

        if (
            isset($args['grand_total_from'])
            && is_numeric($args['grand_total_from'])
        ) {
            $query->where('orders.grand_total', '>=', (float) $args['grand_total_from']);
        }

        if (
            isset($args['grand_total_to'])
            && is_numeric($args['grand_total_to'])
        ) {
            $query->where('orders.grand_total', '<=', (float) $args['grand_total_to']);
        }

        if (! empty($args['created_at_from'])) {
            $query->whereDate('orders.created_at', '>=', $args['created_at_from']);
        }

        if (! empty($args['created_at_to'])) {
            $query->whereDate('orders.created_at', '<=', $args['created_at_to']);
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy(self::SORT_COLUMNS[$column] ?? self::SORT_COLUMNS['id'], $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminReturnableOrder
    {
        $ordered = (int) $row->total_qty_ordered;
        $returned = (int) $row->total_rma_qty;

        $order = new AdminReturnableOrder;
        $order->id = (int) $row->id;
        $order->increment_id = $row->increment_id !== null ? (string) $row->increment_id : null;
        $order->status = $row->status;
        $order->customer_name = trim((string) $row->customer_name) ?: null;
        $order->is_guest = (bool) $row->is_guest;
        $order->grand_total = $row->grand_total !== null ? (float) $row->grand_total : null;
        $order->formatted_grand_total = $row->grand_total !== null
            ? core()->formatPrice($row->grand_total, $row->order_currency_code)
            : null;
        $order->order_currency_code = $row->order_currency_code;
        $order->method_title = $row->method_title;
        $order->total_qty_ordered = $ordered;
        $order->total_rma_qty = $returned;
        $order->returnable_qty = max(0, $ordered - $returned);
        $order->created_at = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;

        return $order;
    }
}
