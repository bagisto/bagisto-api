<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\User\Models\Admin;

class AdminOrderActionGuard
{
    /**
     * The permission needed to cancel an order.
     */
    public const PERM_CANCEL = 'sales.orders.cancel';

    /**
     * The permission needed to invoice an order.
     */
    public const PERM_INVOICE = 'sales.invoices.create';

    /**
     * The permission needed to ship an order.
     */
    public const PERM_SHIPMENT = 'sales.shipments.create';

    /**
     * The permission needed to refund an order.
     */
    public const PERM_REFUND = 'sales.refunds.create';

    /**
     * Create a new instance.
     */
    public function __construct(protected OrderRepository $orderRepository) {}

    /**
     * Resolve the admin from the current request's Bearer token.
     *
     * @throws AuthenticationException
     */
    public function resolveAdmin(): Admin
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return $admin;
    }

    /**
     * Resolve the order from the URI variables, the GraphQL arguments or the route.
     *
     * @param  string  $routeKey  The placeholder name, such as `id` or `orderId`.
     */
    public function resolveOrder(array $uriVariables, array $context, string $routeKey = 'id'): Order
    {
        $raw = $uriVariables[$routeKey]
            ?? $uriVariables['orderId']
            ?? $context['args']['input']['orderId']
            ?? $context['args']['orderId']
            ?? $context['args']['input']['id']
            ?? $context['args']['id']
            ?? request()->route($routeKey)
            ?? request()->route('orderId')
            ?? request()->input('orderId')
            ?? null;

        if (
            $raw === null
            || $raw === ''
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $id = (int) basename((string) $raw);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $order = $this->orderRepository
            ->with(['items.product', 'items.children', 'payment', 'channel', 'addresses', 'refunds'])
            ->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        return $order;
    }

    /**
     * Assert the admin may cancel the order, checking the permission before any order state.
     */
    public function assertCanCancel(Order $order, Admin $admin): void
    {
        $this->assertPermission($admin, self::PERM_CANCEL, 'bagistoapi::app.admin.order.actions.cancel.no-permission');

        $this->assertStatusOpen($order, 'cancel');

        if (! $this->hasAnyQty($order, 'qty_to_cancel')) {
            $key = $this->hasBeenProcessed($order)
                ? 'cancel.already-processed'
                : 'cancel.nothing-to-cancel';

            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.'.$key), 422);
        }
    }

    /**
     * Assert the admin may invoice the order, checking the permission before any order state.
     */
    public function assertCanInvoice(Order $order, Admin $admin): void
    {
        $this->assertPermission($admin, self::PERM_INVOICE, 'bagistoapi::app.admin.order.actions.invoice.no-permission');

        $this->assertStatusOpen($order, 'invoice');

        if (
            $order->payment
            && $order->payment->method === 'paypal_standard'
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.paypal-standard-blocked'), 422);
        }

        if (! $this->hasAnyQty($order, 'qty_to_invoice')) {
            $key = $order->invoices()->exists()
                ? 'bagistoapi::app.admin.order.actions.invoice.already-invoiced'
                : 'bagistoapi::app.admin.order.actions.invoice.nothing-to-invoice';

            throw new InvalidInputException(__($key), 422);
        }
    }

    /**
     * Assert the admin may ship the order, checking the permission before any order state.
     */
    public function assertCanShip(Order $order, Admin $admin): void
    {
        $this->assertPermission($admin, self::PERM_SHIPMENT, 'bagistoapi::app.admin.order.actions.shipment.no-permission');

        $this->assertStatusOpen($order, 'shipment');

        $shippable = false;

        foreach ($order->items as $item) {
            if (
                (int) $item->qty_to_ship > 0
                && $item->getTypeInstance()->isStockable()
            ) {
                $shippable = true;
                break;
            }
        }

        if (! $shippable) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.nothing-to-ship'), 422);
        }
    }

    /**
     * Assert the admin may refund the order, checking the permission before any order state.
     */
    public function assertCanRefund(Order $order, Admin $admin): void
    {
        $this->assertPermission($admin, self::PERM_REFUND, 'bagistoapi::app.admin.order.actions.refund.no-permission');

        $this->assertStatusOpen($order, 'refund');

        $hasRefundableQty = $this->hasAnyQty($order, 'qty_to_refund');

        $outstanding = (float) $order->base_grand_total_invoiced
            - (float) $order->base_grand_total_refunded
            - (float) $order->refunds()->sum('base_adjustment_fee');

        if (
            ! $hasRefundableQty
            && $outstanding <= 0
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.nothing-to-refund'), 422);
        }
    }

    /**
     * Whether the admin's role grants the permission.
     */
    public function adminHasPermission(Admin $admin, string $permission): bool
    {
        return AdminAuthHelper::hasPermission($admin, $permission);
    }

    /**
     * Refuse with 403 when the admin's role lacks the permission.
     *
     * @throws AuthorizationException
     */
    public function assertPermission(Admin $admin, string $permission, string $messageKey): void
    {
        if (! $this->adminHasPermission($admin, $permission)) {
            throw new AuthorizationException(__($messageKey));
        }
    }

    /**
     * Refuse closed and fraud orders, with a reason for each action.
     */
    protected function assertStatusOpen(Order $order, string $action): void
    {
        if ($order->status === Order::STATUS_CLOSED) {
            throw new InvalidInputException(
                __("bagistoapi::app.admin.order.actions.{$action}.closed"),
                422,
            );
        }

        if ($order->status === Order::STATUS_FRAUD) {
            throw new InvalidInputException(
                __("bagistoapi::app.admin.order.actions.{$action}.fraud"),
                422,
            );
        }
    }

    /**
     * Whether at least one item has a positive value for the quantity attribute.
     */
    protected function hasAnyQty(Order $order, string $attribute): bool
    {
        foreach ($order->items as $item) {
            if ((int) $item->{$attribute} > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether any item of the order has been invoiced or shipped.
     */
    protected function hasBeenProcessed(Order $order): bool
    {
        return $this->hasAnyQty($order, 'qty_invoiced')
            || $this->hasAnyQty($order, 'qty_shipped');
    }
}
