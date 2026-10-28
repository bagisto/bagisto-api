<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\ReturnableOrder;
use Webkul\Sales\Models\Order;

class ReturnableOrderProvider implements ProviderInterface
{
    private const SORTABLE = [
        'created_at' => 'orders.created_at',
        'increment_id' => 'orders.increment_id',
        'grand_total' => 'orders.grand_total',
    ];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $args = $context['args'] ?? [];

        $prefix = DB::getTablePrefix();

        $returnedPerItem = DB::table('rma_items')
            ->select('order_item_id', DB::raw('SUM(quantity) as total_rma_qty'))
            ->groupBy('order_item_id');

        $query = DB::table('orders')
            ->select([
                'orders.id',
                'orders.increment_id',
                'orders.status',
                'orders.created_at',
                'orders.grand_total',
                'orders.order_currency_code',
                'order_payment.method_title',
                DB::raw("SUM({$prefix}order_items.qty_ordered) as total_qty_ordered"),
                DB::raw("COALESCE(SUM({$prefix}rma_items_agg.total_rma_qty), 0) as total_rma_qty"),
            ])
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoinSub($returnedPerItem, 'rma_items_agg', 'order_items.id', '=', 'rma_items_agg.order_item_id')
            ->where('orders.customer_id', $customer->id)
            ->whereNotIn('orders.status', [
                Order::STATUS_CANCELED,
                Order::STATUS_CLOSED,
                Order::STATUS_FRAUD,
                Order::STATUS_PENDING_PAYMENT,
            ])
            ->whereNotNull('order_items.rma_return_period')
            ->whereRaw("DATEDIFF(NOW(), {$prefix}order_items.created_at) <= {$prefix}order_items.rma_return_period")
            ->groupBy('orders.id')
            ->havingRaw("SUM({$prefix}order_items.qty_ordered) > COALESCE(SUM({$prefix}rma_items_agg.total_rma_qty), 0)");

        $incrementId = $args['incrementId'] ?? request()->query('increment_id');

        if (! empty($incrementId)) {
            $query->where('orders.increment_id', 'like', '%'.$incrementId.'%');
        }

        $status = $args['status'] ?? request()->query('status');

        if (! empty($status)) {
            $query->where('orders.status', (string) $status);
        }

        $sort = $args['sort'] ?? request()->query('sort', 'created_at');
        $direction = strtolower((string) ($args['order'] ?? request()->query('order', 'desc'))) === 'asc' ? 'asc' : 'desc';

        $query->orderBy(self::SORTABLE[$sort] ?? self::SORTABLE['created_at'], $direction);

        return collect($query->get())
            ->map(function ($row) {
                $ordered = (int) $row->total_qty_ordered;
                $returned = (int) $row->total_rma_qty;

                $order = new ReturnableOrder;
                $order->id = (int) $row->id;
                $order->increment_id = (string) $row->increment_id;
                $order->status = $row->status;
                $order->status_label = $row->status ? __('shop::app.customers.account.orders.status.'.$row->status) : null;
                $order->grand_total = $row->grand_total !== null ? (float) $row->grand_total : null;
                $order->formatted_grand_total = $this->formatted($row->grand_total, $row->order_currency_code);
                $order->order_currency_code = $row->order_currency_code;
                $order->payment_method_title = $row->method_title;
                $order->total_qty_ordered = $ordered;
                $order->total_returned_qty = $returned;
                $order->returnable_qty = max(0, $ordered - $returned);
                $order->created_at = $row->created_at ? (string) $row->created_at : null;

                return $order;
            })
            ->values()
            ->all();
    }

    private function formatted(mixed $amount, ?string $currencyCode): ?string
    {
        if ($amount === null) {
            return null;
        }

        try {
            return core()->formatPrice($amount, $currencyCode);
        } catch (Throwable) {
            return (string) $amount;
        }
    }
}
