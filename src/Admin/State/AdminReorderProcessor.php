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
use Webkul\User\Models\Admin;

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

        // Check (A1): guest orders cannot be reordered.
        if ((int) $order->is_guest === 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.guest-not-supported'),
                422,
            );
        }

        // Check (A2): every item's product must still be saleable.
        if (! $this->allItemsSaleable($order)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.items-not-saleable'),
                422,
            );
        }

        // Check (B): admin's role must include `sales.orders.create`.
        if (! $this->adminCanCreateOrders($admin)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.no-permission'),
                422,
            );
        }

        // Check (C): admin reorder must be enabled in store settings.
        if (! $this->adminReorderEnabled()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.reorder.disabled-in-settings'),
                422,
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
     * Check (B): replicate `bouncer()->hasPermission('sales.orders.create')`
     * for the API-token-resolved admin. The core `Admin::hasPermission()`
     * checks `in_array($permission, $this->role->permissions)` and returns
     * false for `permission_type=custom` with empty permissions. We follow
     * the same semantics, plus treat `permission_type=all` as unconditional
     * access (the admin-panel ACL middleware does the same in practice).
     */
    protected function adminCanCreateOrders(Admin $admin): bool
    {
        $role = $admin->role;

        if (! $role) {
            return false;
        }

        if ($role->permission_type === 'all') {
            return true;
        }

        $permissions = $role->permissions;

        if (empty($permissions)) {
            return false;
        }

        if (is_string($permissions)) {
            $permissions = array_filter(array_map('trim', explode(',', $permissions)));
        }

        return in_array('sales.orders.create', (array) $permissions, true);
    }

    /**
     * Check (C): `sales.order_settings.reorder.admin` config toggle.
     * `core()->getConfigData()` works in API context — it reads the
     * core_config table directly with no session dependency.
     */
    protected function adminReorderEnabled(): bool
    {
        return (bool) core()->getConfigData('sales.order_settings.reorder.admin');
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
