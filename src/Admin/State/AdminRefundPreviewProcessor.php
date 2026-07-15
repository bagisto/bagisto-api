<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\RefundTotalsSummary;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Repositories\RefundRepository;

/**
 * POST /api/admin/orders/{orderId}/refunds/preview + adminRefundPreview mutation.
 *
 * Same body as Refund create; returns the computed totals only — never writes.
 */
class AdminRefundPreviewProcessor implements ProcessorInterface
{
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected RefundRepository $refundRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RefundTotalsSummary
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $this->guard->assertCanRefund($order, $admin);

        $items = $this->flatItemsMap($this->readField($data, $context, 'items'));
        $shipping = (float) $this->readField($data, $context, 'shipping', 0);
        $adjustmentRefund = (float) $this->readField($data, $context, 'adjustmentRefund', 0);
        $adjustmentFee = (float) $this->readField($data, $context, 'adjustmentFee', 0);

        try {
            $totals = $this->refundRepository->getOrderItemsRefundSummary([
                'items' => $items,
                'shipping' => $shipping,
                'adjustment_refund' => $adjustmentRefund,
                'adjustment_fee' => $adjustmentFee,
            ], $order->id);
        } catch (\Throwable $e) {
            throw new InvalidInputException($e->getMessage(), 422, $e);
        }

        $summary = new RefundTotalsSummary;
        $summary->orderId = $order->id;
        $summary->subtotal = (float) $totals['subtotal']['price'];
        $summary->formattedSubtotal = (string) ($totals['subtotal']['formatted_price'] ?? '');
        $summary->discount = (float) $totals['discount']['price'];
        $summary->formattedDiscount = (string) ($totals['discount']['formatted_price'] ?? '');
        $summary->tax = (float) $totals['tax']['price'];
        $summary->formattedTax = (string) ($totals['tax']['formatted_price'] ?? '');
        $summary->shipping = (float) $totals['shipping']['price'];
        $summary->formattedShipping = (string) ($totals['shipping']['formatted_price'] ?? '');
        $summary->adjustmentRefund = $adjustmentRefund;
        $summary->adjustmentFee = $adjustmentFee;
        $summary->grandTotal = (float) $totals['grand_total']['price'];
        $summary->formattedGrandTotal = (string) ($totals['grand_total']['formatted_price'] ?? '');

        return $summary;
    }

    protected function readField(mixed $data, array $context, string $name, mixed $default = null): mixed
    {
        if (is_object($data) && property_exists($data, $name) && $data->{$name} !== null) {
            if ($name === 'items' && is_array($data->{$name})) {
                return array_map(fn ($i) => is_object($i) ? get_object_vars($i) : (array) $i, $data->{$name});
            }

            return $data->{$name};
        }

        return $context['args']['input'][$name]
            ?? request()->input($name)
            ?? $default;
    }
}
