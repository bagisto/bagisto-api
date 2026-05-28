<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminOrder;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\Sales\Models\Order;

/**
 * Provides the admin Orders listing for REST GET /api/admin/orders and the
 * GraphQL adminOrders query.
 *
 * Returns a Paginator of slim AdminOrder rows. For REST the
 * AdminCollectionEnvelopeNormalizer wraps it as `{ data, meta }`; for GraphQL
 * API Platform applies native cursor pagination.
 */
class OrderCollectionProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    protected const SORTABLE = ['id', 'increment_id', 'status', 'grand_total', 'base_grand_total', 'created_at'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $args = $context['args'] ?? [];

        [$perPage, $page] = $this->resolvePaging($args);

        $query = Order::query()->with([
            'items.product.images',
            'addresses',
            'payment',
        ]);

        $this->applyFilters($query, $args);
        $this->applySort($query);

        $total = (clone $query)->count();

        $orders = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn (Order $order) => $this->toAdminOrder($order))
            ->all();

        return new Paginator(
            new LengthAwarePaginator($orders, $total, $perPage, $page, ['path' => request()->url()])
        );
    }

    /**
     * Resolve page size + page number from GraphQL cursor args or REST query.
     *
     * @return array{0: int, 1: int}
     */
    protected function resolvePaging(array $args): array
    {
        if (isset($args['first']) || isset($args['after'])) {
            $perPage = (int) ($args['first'] ?? self::DEFAULT_PER_PAGE);
            $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

            $offset = 0;
            if ($after = $args['after'] ?? null) {
                $decoded = base64_decode($after, true);
                $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
            }

            return [$perPage, (int) floor($offset / $perPage) + 1];
        }

        $perPage = (int) (request()->query('per_page') ?: self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));
        $page = max(1, (int) (request()->query('page') ?: 1));

        return [$perPage, $page];
    }

    /**
     * Read a filter value from GraphQL args, falling back to the REST query.
     */
    protected function filterValue(array $args, string $key): mixed
    {
        return $args[$key] ?? request()->query($key);
    }

    /**
     * Apply the 7 listing filters: order ID, status, grand total, channel,
     * customer, email, and date (preset or custom range).
     */
    protected function applyFilters($query, array $args): void
    {
        if ($orderId = $this->filterValue($args, 'order_id')) {
            $query->where('increment_id', 'like', '%'.$orderId.'%');
        }

        if ($status = $this->filterValue($args, 'status')) {
            $query->where('status', $status);
        }

        $grandTotal = $this->filterValue($args, 'grand_total');
        if ($grandTotal !== null && $grandTotal !== '') {
            $query->where('grand_total', $grandTotal);
        }

        $channel = $this->filterValue($args, 'channel');
        if ($channel !== null && $channel !== '') {
            $query->where('channel_id', $channel);
        }

        if ($customer = $this->filterValue($args, 'customer')) {
            $query->whereRaw("CONCAT(customer_first_name, ' ', customer_last_name) like ?", ['%'.$customer.'%']);
        }

        if ($email = $this->filterValue($args, 'email')) {
            $query->where('customer_email', 'like', '%'.$email.'%');
        }

        [$from, $to] = $this->resolveDateRange($args);

        if ($from) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', $to->endOfDay());
        }
    }

    /**
     * Resolve the date filter to a [from, to] Carbon pair.
     *
     * Explicit date_from / date_to wins; otherwise a date_range preset is
     * expanded. Returns [null, null] when no date filter is set.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveDateRange(array $args): array
    {
        $from = $this->filterValue($args, 'date_from');
        $to = $this->filterValue($args, 'date_to');

        if ($from || $to) {
            return [
                $from ? Carbon::parse($from) : null,
                $to ? Carbon::parse($to) : null,
            ];
        }

        $now = Carbon::now();

        return match ($this->filterValue($args, 'date_range')) {
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

    /**
     * Apply sorting — defaults to newest first.
     */
    protected function applySort($query): void
    {
        $sort = request()->query('sort');
        $order = strtolower((string) request()->query('order')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy(in_array($sort, self::SORTABLE, true) ? $sort : 'created_at', $order);
    }

    /**
     * Map an Order model to the slim AdminOrder row.
     */
    protected function toAdminOrder(Order $order): AdminOrder
    {
        $row = new AdminOrder;

        $row->id = $order->id;
        $row->increment_id = $order->increment_id;
        $row->status = $order->status;
        $row->status_label = $order->status_label;
        $row->channel_id = $order->channel_id;
        $row->channel_name = $order->channel_name;
        $row->is_guest = (bool) $order->is_guest;
        $row->customer_id = $order->customer_id;
        $row->customer_email = $order->customer_email;
        $row->customer_name = trim($order->customer_first_name.' '.$order->customer_last_name);
        $row->payment_title = $this->paymentTitle($order);
        $row->coupon_code = $order->coupon_code;
        $row->total_item_count = $order->total_item_count;
        $row->total_qty_ordered = (int) $order->total_qty_ordered;
        $row->order_currency_code = $order->order_currency_code;
        $row->grand_total = (float) $order->grand_total;
        $row->base_grand_total = (float) $order->base_grand_total;
        $row->formatted_grand_total = $this->safeFormatPrice($order->grand_total, $order->order_currency_code);
        $row->location = $this->billingLocation($order);
        $row->created_at = (string) $order->created_at;
        $row->updated_at = (string) $order->updated_at;
        $row->items = $order->items->map(fn ($orderItem) => $this->toItemPreview($orderItem))->all();

        return $row;
    }

    /**
     * Returns the raw amount as a string when the order's currency code
     * doesn't match any row in the `currencies` table (otherwise
     * core()->formatPrice would TypeError on a null Currency).
     */
    protected function safeFormatPrice($amount, ?string $code): string
    {
        try {
            return core()->formatPrice($amount, $code);
        } catch (\Throwable $e) {
            return (string) $amount;
        }
    }

    protected function toItemPreview($orderItem): array
    {
        $image = $orderItem->product?->images?->first();

        return [
            'id'           => $orderItem->id,
            'sku'          => $orderItem->sku,
            'name'         => $orderItem->name,
            'qtyOrdered'   => (int) $orderItem->qty_ordered,
            'productImage' => $image ? Storage::url($image->path) : null,
        ];
    }

    /**
     * Resolve the payment-method display title from core config.
     */
    protected function paymentTitle(Order $order): ?string
    {
        $method = $order->payment?->method;

        if (! $method) {
            return null;
        }

        return core()->getConfigData('sales.payment_methods.'.$method.'.title') ?: $method;
    }

    /**
     * Build a "City, State, Country" string from the billing address.
     */
    protected function billingLocation(Order $order): ?string
    {
        $address = $order->billing_address;

        if (! $address) {
            return null;
        }

        return collect([$address->city, $address->state, $address->country])
            ->filter()
            ->join(', ') ?: null;
    }
}
