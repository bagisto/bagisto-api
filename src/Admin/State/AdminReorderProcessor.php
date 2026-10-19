<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminReorder;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Models\Order;

class AdminReorderProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(private readonly AdminOrderActionGuard $actionGuard) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminReorder
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
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

        if ((int) $order->is_guest === 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.guest-not-supported'),
                422,
            );
        }

        if (! $this->allItemsSaleable($order)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.items-not-saleable'),
                422,
            );
        }

        if (! $this->actionGuard->adminHasPermission($admin, 'sales.orders.create')) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.no-permission'),
                422,
            );
        }

        if (! $this->adminReorderEnabled()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.disabled-in-settings'),
                422,
            );
        }

        try {
            $cart = Cart::createCart([
                'customer' => $order->customer,
                'is_active' => false,
            ]);

            Cart::setCart($cart);

            $this->addOrderItems($order, $orderId);

            return $this->result(
                id: $orderId,
                success: true,
                message: __('bagistoapi::app.admin.order.reorder.success'),
                cartId: $cart->id,
            );
        } catch (\Throwable $exception) {
            Log::error('Reorder: cart build failed', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
            ]);

            return $this->result(
                id: $orderId,
                success: false,
                message: __('bagistoapi::app.admin.order.reorder.failed'),
            );
        }
    }

    /**
     * Re-add every order item to the current cart, logging per-item failures.
     */
    protected function addOrderItems(Order $order, int $orderId): void
    {
        foreach ($order->items as $item) {
            try {
                Cart::addProduct($item->product, $item->additional);
            } catch (\Throwable $exception) {
                Log::warning('Reorder: failed to add item', [
                    'order_id' => $orderId,
                    'item_id' => $item->id,
                    'error' => $exception->getMessage(),
                ]);
            }
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

        if (
            $raw === null
            || $raw === ''
        ) {
            return null;
        }

        return (int) basename((string) $raw);
    }

    /**
     * Check (A2): every item's product must still be saleable. Mirrors the
     * second half of `Order::canReorder()` so we can surface a distinct error.
     */
    protected function allItemsSaleable(Order $order): bool
    {
        foreach ($order->items as $item) {
            if (! $item->product?->getTypeInstance()->isSaleable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check (C): `sales.order_settings.reorder.admin` config toggle.
     * `core()->getConfigData()` works in API context — it reads the.
     */
    protected function adminReorderEnabled(): bool
    {
        return (bool) core()->getConfigData('sales.order_settings.reorder.admin');
    }

    /**
     * Result.
     */
    protected function result(int $id, bool $success, string $message, ?int $cartId = null): AdminReorder
    {
        $reorder = new AdminReorder;
        $reorder->id = $id;
        $reorder->success = $success;
        $reorder->message = $message;
        $reorder->cartId = $cartId;

        return $reorder;
    }
}
