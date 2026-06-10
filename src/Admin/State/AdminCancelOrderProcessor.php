<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminCancelOrder;
use Webkul\BagistoApi\Admin\Models\OrderDetail;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

/**
 * POST /api/admin/orders/{id}/cancel + createAdminCancelOrder mutation.
 *
 * Eligibility checks delegated to the shared AdminOrderActionGuard. On
 * success calls `OrderRepository::cancel`, reloads the order, and returns it
 * via the same `OrderDetailProvider::toDetail()` helper used by the GET
 * order-detail endpoint — so the response matches the order-view payload
 * shape exactly.
 */
class AdminCancelOrderProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected OrderRepository $orderRepository,
        protected OrderDetailProvider $detailProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context);

        $this->guard->assertCanCancel($order, $admin);

        $result = $this->orderRepository->cancel($order->id);

        if (! $result) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.cancel.failed'), 422);
        }

        $reloaded = Order::with([
            'customer.group', 'channel', 'addresses', 'payment',
            'items.product', 'items.child', 'items.children',
            'items.downloadable_link_purchased', 'invoices', 'shipments',
        ])->find($order->id);

        // GraphQL exposes the resource's own type (AdminCancelOrder), which can't
        // be the full OrderDetail — return a slim order summary there. REST keeps
        // returning the full OrderDetail payload (output: OrderDetail on the Post).
        if ($operation instanceof GraphQlOperation) {
            return $this->toSummary($reloaded);
        }

        return $this->detailProvider->toDetail($reloaded);
    }

    protected function toSummary(Order $order): AdminCancelOrder
    {
        $summary = new AdminCancelOrder;
        $summary->id = $order->id;
        $summary->orderId = $order->id;
        $summary->incrementId = $order->increment_id;
        $summary->status = $order->status;
        $summary->statusLabel = $order->status_label;
        $summary->grandTotal = $order->grand_total !== null ? (float) $order->grand_total : null;
        $summary->baseGrandTotal = $order->base_grand_total !== null ? (float) $order->base_grand_total : null;
        $summary->totalQtyOrdered = $order->total_qty_ordered !== null ? (int) $order->total_qty_ordered : null;
        $summary->success = true;
        $summary->message = __('bagistoapi::app.admin.order.actions.cancel.success');

        return $summary;
    }
}
