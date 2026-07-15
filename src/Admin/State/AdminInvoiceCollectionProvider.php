<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceRestDto;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderAddress;
use Webkul\Sales\Models\Order;

/**
 * GET /api/admin/invoices + adminInvoices cursor query.
 *
 * Filters: id (exact/list), order_id (partial on increment_id), state,
 * base_grand_total (exact or range via base_grand_total_from/to), and
 * created_at (range / preset). Sort: id (default desc), increment_id,
 * order_id, base_grand_total, state, created_at.
 *
 * Returns the full AdminInvoice resource per row so EVERY invoice column has its
 * real value over both transports (clients pick which they query). Only the
 * relation-derived fields (items / billingAddress / shippingAddress) stay null
 * on the listing — they need per-row queries; use GET /api/admin/invoices/{id}
 * for those.
 */
class AdminInvoiceCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;
    use MapsOrderAddress;

    protected const PERMISSION = 'sales.invoices.view';

    /** Per-page address caches, keyed by order_id (batch-loaded in mapRows). */
    private array $billingByOrder = [];

    private array $shippingByOrder = [];

    /** Set per request so mapRow can return the Eloquent model (GraphQL) or the DTO (REST). */
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

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
            // All invoice columns + the cheap order-context columns from the join.
            ->select('invoices.*')
            ->addSelect(
                'orders.increment_id as order_increment_id',
                'orders.customer_email as order_customer_email',
                'orders.customer_first_name as order_customer_first_name',
                'orders.customer_last_name as order_customer_last_name',
                'orders.status as order_status',
                'orders.channel_name as order_channel_name',
                'orders.created_at as order_created_at',
            )
            ->selectRaw("CASE WHEN {$prefix}invoices.increment_id IS NOT NULL THEN {$prefix}invoices.increment_id ELSE {$prefix}invoices.id END AS resolved_increment_id")
            // Surface the linked order-transaction id when the invoice column is
            // null (the "Create Transaction" path records it in order_transactions).
            ->selectRaw("COALESCE({$prefix}invoices.transaction_id, (SELECT ot.transaction_id FROM {$prefix}order_transactions ot WHERE ot.invoice_id = {$prefix}invoices.id LIMIT 1)) AS resolved_transaction_id");
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

    /**
     * Batch-load the billing + shipping address for every order on this page in a
     * single query, then map each row — no per-row N+1.
     */
    protected function mapRows($rows): array
    {
        $this->billingByOrder = [];
        $this->shippingByOrder = [];

        $orderIds = $rows->pluck('order_id')->filter()->unique()->values()->all();

        if (! empty($orderIds)) {
            $addresses = DB::table('addresses')
                ->whereIn('order_id', $orderIds)
                ->whereIn('address_type', ['order_billing', 'order_shipping'])
                ->get();

            foreach ($addresses as $address) {
                if ($address->address_type === 'order_billing' && ! isset($this->billingByOrder[$address->order_id])) {
                    $this->billingByOrder[$address->order_id] = $address;
                } elseif ($address->address_type === 'order_shipping' && ! isset($this->shippingByOrder[$address->order_id])) {
                    $this->shippingByOrder[$address->order_id] = $address;
                }
            }
        }

        return $rows->map(fn ($row) => $this->mapRow($row))->all();
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $currency = $row->order_currency_code ?? null;

        $dto = new AdminInvoiceRestDto;
        $dto->id = (int) $row->id;
        $dto->incrementId = $row->resolved_increment_id ?? $row->increment_id ?? (string) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->state = $row->state;
        $dto->emailSent = $row->email_sent !== null ? (bool) $row->email_sent : null;
        $dto->totalQty = $row->total_qty !== null ? (int) $row->total_qty : null;

        // --- Currency codes ---
        $dto->orderCurrencyCode = $row->order_currency_code;
        $dto->baseCurrencyCode = $row->base_currency_code;
        $dto->channelCurrencyCode = $row->channel_currency_code;

        // --- Sub-total ---
        $dto->subTotal = $this->num($row->sub_total);
        $dto->formattedSubTotal = $this->money($row->sub_total, $currency);
        $dto->baseSubTotal = $this->num($row->base_sub_total);
        $dto->formattedBaseSubTotal = $this->baseMoney($row->base_sub_total);
        $dto->subTotalInclTax = $this->num($row->sub_total_incl_tax);
        $dto->formattedSubTotalInclTax = $this->money($row->sub_total_incl_tax, $currency);
        $dto->baseSubTotalInclTax = $this->num($row->base_sub_total_incl_tax);
        $dto->formattedBaseSubTotalInclTax = $this->baseMoney($row->base_sub_total_incl_tax);

        // --- Grand total ---
        $dto->grandTotal = $this->num($row->grand_total);
        $dto->formattedGrandTotal = $this->money($row->grand_total, $currency);
        $dto->baseGrandTotal = $this->num($row->base_grand_total);
        $dto->formattedBaseGrandTotal = $this->baseMoney($row->base_grand_total);

        // --- Tax ---
        $dto->taxAmount = $this->num($row->tax_amount);
        $dto->formattedTaxAmount = $this->money($row->tax_amount, $currency);
        $dto->baseTaxAmount = $this->num($row->base_tax_amount);
        $dto->formattedBaseTaxAmount = $this->baseMoney($row->base_tax_amount);

        // --- Discount ---
        $dto->discountAmount = $this->num($row->discount_amount);
        $dto->formattedDiscountAmount = $this->money($row->discount_amount, $currency);
        $dto->baseDiscountAmount = $this->num($row->base_discount_amount);
        $dto->formattedBaseDiscountAmount = $this->baseMoney($row->base_discount_amount);

        // --- Shipping ---
        $dto->shippingAmount = $this->num($row->shipping_amount);
        $dto->formattedShippingAmount = $this->money($row->shipping_amount, $currency);
        $dto->baseShippingAmount = $this->num($row->base_shipping_amount);
        $dto->formattedBaseShippingAmount = $this->baseMoney($row->base_shipping_amount);
        $dto->shippingAmountInclTax = $this->num($row->shipping_amount_incl_tax);
        $dto->formattedShippingAmountInclTax = $this->money($row->shipping_amount_incl_tax, $currency);
        $dto->baseShippingAmountInclTax = $this->num($row->base_shipping_amount_incl_tax);
        $dto->formattedBaseShippingAmountInclTax = $this->baseMoney($row->base_shipping_amount_incl_tax);
        $dto->shippingTaxAmount = $this->num($row->shipping_tax_amount);
        $dto->formattedShippingTaxAmount = $this->money($row->shipping_tax_amount, $currency);
        $dto->baseShippingTaxAmount = $this->num($row->base_shipping_tax_amount);
        $dto->formattedBaseShippingTaxAmount = $this->baseMoney($row->base_shipping_tax_amount);

        $dto->transactionId = $row->resolved_transaction_id ?? $row->transaction_id;
        $dto->reminders = $row->reminders !== null ? (int) $row->reminders : null;
        $dto->nextReminderAt = $row->next_reminder_at ? (string) $row->next_reminder_at : null;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;
        $dto->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        // --- Order / customer context (cheap — already joined to orders) ---
        $name = trim((string) ($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));
        $dto->customerName = $name !== '' ? $name : null;
        $dto->customerEmail = $row->order_customer_email;
        $dto->orderStatus = $row->order_status;
        $dto->orderStatusLabel = $this->orderStatusLabel($row->order_status);
        $dto->channelName = $row->order_channel_name;
        $dto->orderDate = $row->order_created_at ? (string) $row->order_created_at : null;

        // Order id is cheap (already joined); addresses + line items are
        // detail-only — use GET /api/admin/invoices/{id} for those.
        $dto->order = ['id' => $row->order_id !== null ? (int) $row->order_id : null];
        $dto->items = [];

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminInvoice. Order-context columns are
     * pre-filled (so the model's accessors don't re-query per row) and the
     * items / addresses relations are set empty (detail-only on the listing).
     */
    protected function mapRowToEloquent(object $row): AdminInvoice
    {
        $name = trim((string) ($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));

        $model = (new AdminInvoice)->forceFill([
            'id' => (int) $row->id,
            'increment_id' => $row->resolved_increment_id ?? $row->increment_id ?? (string) $row->id,
            'order_id' => $row->order_id !== null ? (int) $row->order_id : null,
            'state' => $row->state,
            'email_sent' => $row->email_sent,
            'total_qty' => $row->total_qty,
            'order_currency_code' => $row->order_currency_code,
            'base_currency_code' => $row->base_currency_code,
            'channel_currency_code' => $row->channel_currency_code,
            'sub_total' => $row->sub_total,
            'base_sub_total' => $row->base_sub_total,
            'sub_total_incl_tax' => $row->sub_total_incl_tax,
            'base_sub_total_incl_tax' => $row->base_sub_total_incl_tax,
            'grand_total' => $row->grand_total,
            'base_grand_total' => $row->base_grand_total,
            'tax_amount' => $row->tax_amount,
            'base_tax_amount' => $row->base_tax_amount,
            'discount_amount' => $row->discount_amount,
            'base_discount_amount' => $row->base_discount_amount,
            'shipping_amount' => $row->shipping_amount,
            'base_shipping_amount' => $row->base_shipping_amount,
            'shipping_amount_incl_tax' => $row->shipping_amount_incl_tax,
            'base_shipping_amount_incl_tax' => $row->base_shipping_amount_incl_tax,
            'shipping_tax_amount' => $row->shipping_tax_amount,
            'base_shipping_tax_amount' => $row->base_shipping_tax_amount,
            'reminders' => $row->reminders,
            'next_reminder_at' => $row->next_reminder_at,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
            // Pre-set so the accessors use these instead of re-querying the order.
            'transaction_id' => $row->resolved_transaction_id ?? $row->transaction_id,
            'order_increment_id' => $row->order_increment_id,
            'order_status' => $row->order_status,
            'order_date' => $row->order_created_at,
            'channel_name' => $row->order_channel_name,
            'customer_name' => $name !== '' ? $name : null,
            'customer_email' => $row->order_customer_email,
        ]);

        $model->setRelation('items', collect());
        // The full nested order object is detail-only (querying it on the listing
        // would expose OrderDetail's many non-null fields, none of which are loaded
        // here). It resolves null on the listing — use the flat orderIncrementId /
        // orderStatus scalars on the row, or the detail query for the full object.
        $model->setRelation('order', null);

        return $model;
    }

    private function num($v): ?float
    {
        return $v !== null ? (float) $v : null;
    }

    private function money($v, ?string $currency): ?string
    {
        return $v !== null ? $this->safeFormatPrice((float) $v, $currency) : null;
    }

    private function baseMoney($v): ?string
    {
        return $v !== null ? core()->formatBasePrice((float) $v) : null;
    }

    /**
     * Human-readable order status label from the raw status code (no DB query) —
     * reuses the core Order model's status-label map so it matches the detail.
     */
    private function orderStatusLabel(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        try {
            $order = new Order;
            $order->status = $status;

            return $order->status_label;
        } catch (\Throwable $e) {
            return $status;
        }
    }

    /**
     * Format a price in the given currency code, falling back to the base-currency
     * format (and then the raw numeric string) when the code can't be resolved to a
     * Currency row — e.g. an order whose snapshot currency was later deleted.
     */
    protected function safeFormatPrice(float $amount, ?string $currencyCode): string
    {
        try {
            return core()->formatPrice($amount, $currencyCode ?: null);
        } catch (\Throwable $e) {
            try {
                return core()->formatBasePrice($amount);
            } catch (\Throwable $e) {
                return (string) $amount;
            }
        }
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
            'today' => [$now->copy(), $now->copy()],
            'yesterday' => [$now->copy()->subDay(), $now->copy()->subDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$now->copy()->subMonthsNoOverflow(3), $now->copy()],
            'last_6_months' => [$now->copy()->subMonthsNoOverflow(6), $now->copy()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [null, null],
        };
    }
}
