<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminReorder;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Models\Order;

/**
 * Admin Reorder — POST /api/admin/orders/{id}/reorder + GraphQL createAdminReorder.
 *
 * Mirrors the monolith admin Reorder button: build a fresh admin draft cart
 * (`is_active = false`) for the order's customer, re-add every order item
 * via Cart::addProduct($item->product, $item->additional), and return the new
 * cart id. Per-item add failures are swallowed (best-effort), matching core.
 */
class AdminReorderProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminReorder
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $orderId = $this->resolveOrderId($uriVariables, $context);

        if ($orderId === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $order = Order::with(['items.product', 'customer'])->find($orderId);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        if (! $order->canReorder()) {
            return $this->result(
                id: $orderId,
                success: false,
                message: __('bagistoapi::app.admin.order.reorder.cannot-reorder'),
            );
        }

        try {
            $cart = Cart::createCart([
                'customer'  => $order->customer,
                'is_active' => false,
            ]);

            Cart::setCart($cart);

            foreach ($order->items as $item) {
                try {
                    Cart::addProduct($item->product, $item->additional);
                } catch (\Throwable $e) {
                    // Per-item failures are swallowed (matches the monolith).
                    Log::warning('Reorder: failed to add item', [
                        'order_id' => $orderId,
                        'item_id'  => $item->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            return $this->result(
                id: $orderId,
                success: true,
                message: __('bagistoapi::app.admin.order.reorder.success'),
                cartId: $cart->id,
            );
        } catch (\Throwable $e) {
            Log::error('Reorder: cart build failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return $this->result(
                id: $orderId,
                success: false,
                message: __('bagistoapi::app.admin.order.reorder.failed'),
            );
        }
    }

    /**
     * Order id from REST uriVariables, GraphQL args, or the request body.
     */
    protected function resolveOrderId(array $uriVariables, array $context): ?int
    {
        $raw = $uriVariables['id']
            ?? $context['args']['input']['orderId']
            ?? $context['args']['input']['id']
            ?? request()->input('orderId')
            ?? request()->input('id')
            ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        // GraphQL passes the IRI for an `ID!`; strip to the numeric id.
        return (int) basename((string) $raw);
    }

    protected function result(int $id, bool $success, string $message, ?int $cartId = null): AdminReorder
    {
        $r = new AdminReorder;
        $r->id = $id;
        $r->success = $success;
        $r->message = $message;
        $r->cartId = $cartId;

        return $r;
    }
}
