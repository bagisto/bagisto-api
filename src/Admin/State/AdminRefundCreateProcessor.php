<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminRefund;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminRefund;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\RefundRepository;

class AdminRefundCreateProcessor implements ProcessorInterface
{
    use BuildsAdminRefund;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected RefundRepository $refundRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminRefund
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
                'max' => core()->formatBasePrice($maxRefundAmount),
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

        return $this->buildAdminRefund($refund->fresh(['items', 'items.product', 'order', 'order.addresses', 'order.payment']));
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
            'refund' => [
                'items' => $flat,
                'shipping' => $shipping,
                'adjustment_refund' => $adjustmentRefund,
                'adjustment_fee' => $adjustmentFee,
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
                    'sku' => $item->sku,
                    'requested' => $qty,
                    'available' => (int) $item->qty_to_refund,
                ]), 422);
            }
        }
    }
}
