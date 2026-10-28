<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\Checkout\Facades\Cart;

class AdminCartRemoveCouponProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        Cart::setCart($cart);
        Cart::removeCouponCode()->collectTotals();

        return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.coupon-removed'));
    }
}
