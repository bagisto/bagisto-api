<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Admin;

/**
 * Shared auth + eligibility-gate guard for the admin Order Actions wave.
 *
 * One class, used by every Cancel / Invoice / Shipment / Refund / Comment
 * processor + provider. Splits each Order::canX() into specific failure
 * reasons (closed / fraud / nothing-to-X) so the client can disambiguate —
 * mirrors the AdminReorderProcessor "eligibility-split" pattern.
 *
 * Permission resolution is done WITHOUT session: bouncer()->hasPermission()
 * relies on auth()->guard('admin')->user() which Sanctum can't populate.
 * Instead we read $admin->role->permissions directly — same as the Reorder
 * processor.
 */
class AdminOrderActionGuard
{
    public const PERM_CANCEL = 'sales.orders.cancel';

    public const PERM_INVOICE = 'sales.invoices.create';

    public const PERM_SHIPMENT = 'sales.shipments.create';

    public const PERM_REFUND = 'sales.refunds.create';

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
     * Resolve the order id from uriVariables / GraphQL args / route fallback.
     *
     * @param  string  $routeKey  The placeholder name (e.g. `id`, `orderId`).
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

        if ($raw === null || $raw === '') {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $id = (int) basename((string) $raw);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $order = Order::with(['items.product', 'items.children', 'payment', 'channel', 'addresses', 'refunds'])->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        return $order;
    }

    /**
     * Cancel: closed / fraud / nothing-to-cancel / no-permission.
     */
    public function assertCanCancel(Order $order, Admin $admin): void
    {
        $this->assertStatusOpen($order, 'cancel');

        if (! $this->hasAnyQty($order, 'qty_to_cancel')) {
            $key = ($this->hasAnyQty($order, 'qty_invoiced') || $this->hasAnyQty($order, 'qty_shipped'))
                ? 'cancel.already-processed'
                : 'cancel.nothing-to-cancel';

            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.'.$key), 422);
        }

        if (! $this->adminHasPermission($admin, self::PERM_CANCEL)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.cancel.no-permission'), 422);
        }
    }

    /**
     * Invoice: closed / fraud / paypal_standard / nothing-to-invoice / no-permission.
     */
    public function assertCanInvoice(Order $order, Admin $admin): void
    {
        $this->assertStatusOpen($order, 'invoice');

        if ($order->payment && $order->payment->method === 'paypal_standard') {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.paypal-standard-blocked'), 422);
        }

        if (! $this->hasAnyQty($order, 'qty_to_invoice')) {
            $key = $order->invoices()->exists()
                ? 'bagistoapi::app.admin.order.actions.invoice.already-invoiced'
                : 'bagistoapi::app.admin.order.actions.invoice.nothing-to-invoice';

            throw new InvalidInputException(__($key), 422);
        }

        if (! $this->adminHasPermission($admin, self::PERM_INVOICE)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.no-permission'), 422);
        }
    }

    /**
     * Shipment: closed / fraud / nothing-to-ship / no-permission.
     */
    public function assertCanShip(Order $order, Admin $admin): void
    {
        $this->assertStatusOpen($order, 'shipment');

        $shippable = false;
        foreach ($order->items as $item) {
            if ((int) $item->qty_to_ship > 0 && $item->getTypeInstance()->isStockable()) {
                $shippable = true;
                break;
            }
        }

        if (! $shippable) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.nothing-to-ship'), 422);
        }

        if (! $this->adminHasPermission($admin, self::PERM_SHIPMENT)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.no-permission'), 422);
        }
    }

    /**
     * Refund: closed / fraud / nothing-to-refund / no-permission.
     *
     * "Nothing to refund" mirrors Order::canRefund() — at least one item with
     * qty_to_refund > 0, OR an outstanding invoiced-minus-refunded-minus-fees
     * balance.
     */
    public function assertCanRefund(Order $order, Admin $admin): void
    {
        $this->assertStatusOpen($order, 'refund');

        $hasRefundableQty = $this->hasAnyQty($order, 'qty_to_refund');

        $outstanding = (float) $order->base_grand_total_invoiced
            - (float) $order->base_grand_total_refunded
            - (float) $order->refunds()->sum('base_adjustment_fee');

        if (! $hasRefundableQty && $outstanding <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.nothing-to-refund'), 422);
        }

        if (! $this->adminHasPermission($admin, self::PERM_REFUND)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.refund.no-permission'), 422);
        }
    }

    /**
     * Permission check for the API-token-resolved admin (no session).
     */
    public function adminHasPermission(Admin $admin, string $permission): bool
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

        return in_array($permission, (array) $permissions, true);
    }

    /**
     * Block closed/fraud orders, surfacing distinct reasons per action.
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
     * True when at least one item has a positive value for the qty-tracking attr.
     */
    protected function hasAnyQty(Order $order, string $attr): bool
    {
        foreach ($order->items as $item) {
            if ((int) $item->{$attr} > 0) {
                return true;
            }
        }

        return false;
    }
}
