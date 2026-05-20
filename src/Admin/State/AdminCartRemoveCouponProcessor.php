<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\Checkout\Facades\Cart;

/**
 * DELETE /api/admin/carts/{id}/coupon — remove the applied coupon.
 *
 * Mirrors CartController::destroyCoupon. Safe to call even when no coupon is
 * applied — the underlying facade is a no-op in that case.
 */
class AdminCartRemoveCouponProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        Cart::setCart($cart);
        Cart::removeCouponCode()->collectTotals();

        return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.coupon-removed'));
    }
}
