<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRefundDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\RefundRepository;

class AdminRefundCreateProcessor implements ProcessorInterface
{
    use MapsOrderActionItems;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected RefundRepository $refundRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRefundDetailDto
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $this->guard->assertCanRefund($order, $admin);

        $payload = $this->buildPayload($data, $context, $order);
        $this->validateQty($order, $payload['refund']['items']);

        try {
            $totals = $this->refundRepository->getOrderItemsRefundSummary($payload['refund'], $order->id);
        } catch (\Throwable $e) {
            throw new InvalidInputException($e->getMessage(), 422, $e);
        }

        $maxRefundAmount = $totals['grand_total']['price'] - $order->refunds()->sum('base_adjustment_refund');
        $refundAmount = $totals['grand_total']['price'] - $totals['shipping']['price']
            + $payload['refund']['shipping']
            + $payload['refund']['adjustment_refund']
            - $payload['refund']['adjustment_fee'];

        if ($refundAmount <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.amount-zero'), 422);
        }

        if ($refundAmount > $maxRefundAmount) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.amount-exceeds-max', [
                'amount' => core()->formatBasePrice($refundAmount),
                'max'    => core()->formatBasePrice($maxRefundAmount),
            ]), 422);
        }

        try {
            $refund = $this->refundRepository->create($payload);
        } catch (\Throwable $e) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.actions.refund.failed').' '.$e->getMessage(),
                422,
                $e,
            );
        }

        return $this->toDto($refund->fresh(['items', 'order']));
    }

    protected function buildPayload(mixed $data, array $context, Order $order): array
    {
        $itemsRaw = $this->extractItems($data, $context);
        $flat = $this->flatItemsMap($itemsRaw);

        $shipping = (float) $this->extractNumeric($data, $context, 'shipping', 0);
        $adjustmentRefund = (float) $this->extractNumeric($data, $context, 'adjustmentRefund', 0, 'adjustment_refund');
        $adjustmentFee = (float) $this->extractNumeric($data, $context, 'adjustmentFee', 0, 'adjustment_fee');

        return [
            'order_id' => $order->id,
            'refund'   => [
                'items'             => $flat,
                'shipping'          => $shipping,
                'adjustment_refund' => $adjustmentRefund,
                'adjustment_fee'    => $adjustmentFee,
            ],
        ];
    }

    protected function extractItems(mixed $data, array $context): array
    {
        if (is_object($data) && property_exists($data, 'items') && $data->items) {
            return array_map(function ($i) {
                return is_object($i) ? get_object_vars($i) : (array) $i;
            }, (array) $data->items);
        }

        return (array) ($context['args']['input']['items']
            ?? request()->input('items')
            ?? []);
    }

    protected function extractNumeric(mixed $data, array $context, string $camel, float $default, ?string $snake = null): float
    {
        if (is_object($data) && property_exists($data, $camel) && $data->{$camel} !== null) {
            return (float) $data->{$camel};
        }

        $val = $context['args']['input'][$camel]
            ?? request()->input($camel)
            ?? ($snake ? request()->input($snake) : null)
            ?? $default;

        return (float) $val;
    }

    protected function validateQty(Order $order, array $flat): void
    {
        $byId = $order->items->keyBy('id');
        foreach ($flat as $itemId => $qty) {
            $item = $byId->get($itemId);
            if (! $item) {
                continue;
            }
            if ($qty > (int) $item->qty_to_refund) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.qty-exceeds', [
                    'sku'       => $item->sku,
                    'requested' => $qty,
                    'available' => (int) $item->qty_to_refund,
                ]), 422);
            }
        }
    }

    protected function toDto($refund): AdminRefundDetailDto
    {
        $currency = $refund->order_currency_code ?? $refund->order?->order_currency_code ?? '';

        $dto = new AdminRefundDetailDto;
        $dto->id = (int) $refund->id;
        $dto->orderId = (int) $refund->order_id;
        $dto->state = $refund->state;
        $dto->totalQty = (int) $refund->total_qty;
        $dto->orderCurrencyCode = $currency;
        $dto->subTotal = (float) $refund->sub_total;
        $dto->formattedSubTotal = core()->formatPrice((float) $refund->sub_total, $currency);
        $dto->grandTotal = (float) $refund->grand_total;
        $dto->formattedGrandTotal = core()->formatPrice((float) $refund->grand_total, $currency);
        $dto->shippingAmount = (float) $refund->shipping_amount;
        $dto->formattedShippingAmount = core()->formatPrice((float) $refund->shipping_amount, $currency);
        $dto->adjustmentRefund = (float) $refund->adjustment_refund;
        $dto->formattedAdjustmentRefund = core()->formatPrice((float) $refund->adjustment_refund, $currency);
        $dto->adjustmentFee = (float) $refund->adjustment_fee;
        $dto->formattedAdjustmentFee = core()->formatPrice((float) $refund->adjustment_fee, $currency);
        $dto->taxAmount = (float) $refund->tax_amount;
        $dto->formattedTaxAmount = core()->formatPrice((float) $refund->tax_amount, $currency);
        $dto->discountAmount = (float) $refund->discount_amount;
        $dto->formattedDiscountAmount = core()->formatPrice((float) $refund->discount_amount, $currency);
        $dto->createdAt = $refund->created_at ? (string) $refund->created_at : null;
        $dto->updatedAt = $refund->updated_at ? (string) $refund->updated_at : null;

        $dto->items = $refund->items
            ? $refund->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }
}
