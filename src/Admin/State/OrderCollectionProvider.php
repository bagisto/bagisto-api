<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Dto\AdminOrderListDto;
use Webkul\BagistoApi\Admin\Models\AdminOrder;
use Webkul\BagistoApi\Admin\Models\AdminOrderItemPreview;
use Webkul\BagistoApi\Admin\State\Concerns\ResolvesAdminDateRange;
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
    use ResolvesAdminDateRange;

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
        $this->applySort($query, $args);

        $total = (clone $query)->count();

        $isGraphQL = ! empty($context['graphql_operation_name']);

        $orders = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn (Order $order) => $isGraphQL
                ? $this->toAdminOrderEloquent($order)
                : $this->toAdminOrderDto($order))
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

        // Grand total filters the base_grand_total column (the value the admin
        // datagrid shows + sorts on), with optional from/to range support.
        $grandTotal = $this->filterValue($args, 'grand_total');
        if ($grandTotal !== null && $grandTotal !== '') {
            $query->where('base_grand_total', $grandTotal);
        }

        $grandTotalFrom = $this->filterValue($args, 'grand_total_from');
        if ($grandTotalFrom !== null && $grandTotalFrom !== '') {
            $query->where('base_grand_total', '>=', (float) $grandTotalFrom);
        }

        $grandTotalTo = $this->filterValue($args, 'grand_total_to');
        if ($grandTotalTo !== null && $grandTotalTo !== '') {
            $query->where('base_grand_total', '<=', (float) $grandTotalTo);
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

        // Date filter — preset (today, yesterday, this_week, …, last_three_months,
        // last_six_months, this_year) or custom date_from/date_to. Preset keys +
        // ranges match the admin datagrid exactly (see ResolvesAdminDateRange).
        [$from, $to] = $this->resolveAdminDateRange($args);

        if ($from) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', $to->endOfDay());
        }
    }

    /**
     * Apply sorting — defaults to newest first. Reads sort/order from GraphQL
     * args, falling back to the REST query string.
     */
    protected function applySort($query, array $args): void
    {
        $sort = $args['sort'] ?? request()->query('sort');
        $order = strtolower((string) ($args['order'] ?? request()->query('order'))) === 'asc' ? 'asc' : 'desc';

        $query->orderBy(\in_array($sort, self::SORTABLE, true) ? $sort : 'created_at', $order);
    }

    /**
     * Map an Order model to the slim AdminOrder row.
     */
    protected function toAdminOrderEloquent(Order $order): AdminOrder
    {
        $model = (new AdminOrder)->forceFill(array_merge($order->getAttributes(), [
            'id'                    => (int) $order->id,
            'increment_id'          => $order->increment_id,
            'status'                => $order->status,
            'status_label'          => $order->status_label,
            'channel_id'            => $order->channel_id,
            'channel_name'          => $order->channel_name,
            'is_guest'              => (bool) $order->is_guest,
            'customer_id'           => $order->customer_id,
            'customer_email'        => $order->customer_email,
            'customer_name'         => trim($order->customer_first_name.' '.$order->customer_last_name),
            'payment_title'         => $this->paymentTitle($order),
            'coupon_code'           => $order->coupon_code,
            'total_item_count'      => $order->total_item_count,
            'total_qty_ordered'     => (int) $order->total_qty_ordered,
            'order_currency_code'   => $order->order_currency_code,
            'grand_total'           => (float) $order->grand_total,
            'base_grand_total'      => (float) $order->base_grand_total,
            'formatted_grand_total' => $this->safeFormatPrice($order->grand_total, $order->order_currency_code),
            'location'              => $this->billingLocation($order),
            'created_at'            => (string) $order->created_at,
            'updated_at'            => (string) $order->updated_at,
        ]));

        $items = $order->items->map(function ($orderItem) {
            $preview = $this->toItemPreview($orderItem);

            return (new AdminOrderItemPreview)->forceFill([
                'id'            => $preview['id'],
                'sku'           => $preview['sku'],
                'name'          => $preview['name'],
                'qty_ordered'   => $preview['qtyOrdered'],
                'product_image' => $preview['productImage'],
            ]);
        })->values();

        $model->setRelation('items', $items);

        return $model;
    }

    protected function toAdminOrderDto(Order $order): AdminOrderListDto
    {
        $row = new AdminOrderListDto;

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
