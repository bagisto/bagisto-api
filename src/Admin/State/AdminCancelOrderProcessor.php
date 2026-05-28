<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
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

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderDetail
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context);

        $this->guard->assertCanCancel($order, $admin);

        $result = $this->orderRepository->cancel($order->id);

        if (! $result) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.cancel.failed'), 422);
        }

        // Reload after cancel so qty_canceled / status reflect the new state.
        $reloaded = Order::with([
            'customer.group', 'channel', 'addresses', 'payment',
            'items.product', 'items.child', 'items.children',
            'items.downloadable_link_purchased', 'invoices', 'shipments',
        ])->find($order->id);

        return $this->detailProvider->toDetail($reloaded);
    }
}
