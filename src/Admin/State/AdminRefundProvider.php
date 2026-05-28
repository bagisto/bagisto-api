<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminRefundDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Refund;

class AdminRefundProvider implements ProviderInterface
{
    use MapsOrderActionItems;

    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminRefundDetailDto
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.refund.not-found'));
        }

        $refund = Refund::with(['items', 'order'])->find($id);

        if (! $refund) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.refund.not-found'));
        }

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
