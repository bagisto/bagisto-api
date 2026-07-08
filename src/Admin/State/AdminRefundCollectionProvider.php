<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRefund;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderAddress;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;

class AdminRefundCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;
    use MapsOrderAddress;

    protected const PERMISSION = 'sales.refunds.view';

    private array $billingByOrder = [];

    private array $shippingByOrder = [];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
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
            ->select('refunds.*')
            ->addSelect(
                'orders.increment_id as order_increment_id',
                'orders.customer_email as order_customer_email',
                'orders.customer_first_name as order_customer_first_name',
                'orders.customer_last_name as order_customer_last_name',
                'orders.status as order_status',
                'orders.channel_name as order_channel_name',
                'orders.created_at as order_created_at',
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
            'id' => 'refunds.id',
            'order_id' => 'orders.increment_id',
            'state' => 'refunds.state',
            'base_grand_total' => 'refunds.base_grand_total',
            'billed_to' => 'billed_to',
            'created_at' => 'refunds.created_at',
        ];

        $query->orderBy($map[$col] ?? 'refunds.id', $dir);
    }

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

    protected function mapRow(object $row): AdminRefund
    {
        $currency = $row->order_currency_code ?? null;

        $dto = new AdminRefund;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->state = $row->state;
        $dto->emailSent = $row->email_sent !== null ? (bool) $row->email_sent : null;
        $dto->totalQty = $row->total_qty !== null ? (int) $row->total_qty : null;

        $dto->orderCurrencyCode = $row->order_currency_code;
        $dto->baseCurrencyCode = $row->base_currency_code;
        $dto->channelCurrencyCode = $row->channel_currency_code;

        $dto->subTotal = $this->num($row->sub_total);
        $dto->formattedSubTotal = $this->money($row->sub_total, $currency);
        $dto->baseSubTotal = $this->num($row->base_sub_total);
        $dto->formattedBaseSubTotal = $this->baseMoney($row->base_sub_total);
        $dto->subTotalInclTax = $this->num($row->sub_total_incl_tax);
        $dto->formattedSubTotalInclTax = $this->money($row->sub_total_incl_tax, $currency);
        $dto->baseSubTotalInclTax = $this->num($row->base_sub_total_incl_tax);
        $dto->formattedBaseSubTotalInclTax = $this->baseMoney($row->base_sub_total_incl_tax);

        $dto->grandTotal = $this->num($row->grand_total);
        $dto->formattedGrandTotal = $this->money($row->grand_total, $currency);
        $dto->baseGrandTotal = $this->num($row->base_grand_total);
        $dto->formattedBaseGrandTotal = $this->baseMoney($row->base_grand_total);

        $dto->taxAmount = $this->num($row->tax_amount);
        $dto->formattedTaxAmount = $this->money($row->tax_amount, $currency);
        $dto->baseTaxAmount = $this->num($row->base_tax_amount);
        $dto->formattedBaseTaxAmount = $this->baseMoney($row->base_tax_amount);

        $dto->discountAmount = $this->num($row->discount_amount);
        $dto->formattedDiscountAmount = $this->money($row->discount_amount, $currency);
        $dto->baseDiscountAmount = $this->num($row->base_discount_amount);
        $dto->formattedBaseDiscountAmount = $this->baseMoney($row->base_discount_amount);

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

        $dto->adjustmentRefund = $this->num($row->adjustment_refund);
        $dto->formattedAdjustmentRefund = $this->money($row->adjustment_refund, $currency);
        $dto->baseAdjustmentRefund = $this->num($row->base_adjustment_refund);
        $dto->formattedBaseAdjustmentRefund = $this->baseMoney($row->base_adjustment_refund);
        $dto->adjustmentFee = $this->num($row->adjustment_fee);
        $dto->formattedAdjustmentFee = $this->money($row->adjustment_fee, $currency);
        $dto->baseAdjustmentFee = $this->num($row->base_adjustment_fee);
        $dto->formattedBaseAdjustmentFee = $this->baseMoney($row->base_adjustment_fee);

        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;
        $dto->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        $dto->billedTo = trim((string) ($row->billed_to ?? '')) ?: null;

        $name = trim((string) ($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));
        $dto->customerName = $name !== '' ? $name : null;
        $dto->customerEmail = $row->order_customer_email;
        $dto->orderStatus = $row->order_status;
        $dto->orderStatusLabel = $this->orderStatusLabel($row->order_status);
        $dto->channelName = $row->order_channel_name;
        $dto->orderDate = $row->order_created_at ? (string) $row->order_created_at : null;

        $dto->billingAddress = $this->mapAddress($this->billingByOrder[$row->order_id] ?? null);
        $dto->shippingAddress = $this->mapAddress($this->shippingByOrder[$row->order_id] ?? null);

        $dto->items = [];

        return $dto;
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
}
