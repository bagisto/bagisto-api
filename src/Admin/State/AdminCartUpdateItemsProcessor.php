<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

/**
 * PUT /api/admin/carts/{id}/items — bulk-update line-item quantities.
 *
 * Body: { "qty": { "12": 3, "13": 1 } } (mirrors monolith CartController::updateItem).
 */
class AdminCartUpdateItemsProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $qty = $this->resolveQty($data, $context);

        if (empty($qty) || ! is_array($qty)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.qty-required'));
        }

        Cart::setCart($cart);

        try {
            Cart::updateItems(['qty' => $qty]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.item-updated'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart updateItems failed', [
                'cart_id' => $cart->id,
                'qty'     => $qty,
                'error'   => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.item-update-failed'));
        }
    }

    protected function resolveQty(mixed $data, array $context): ?array
    {
        if (is_object($data) && isset($data->qty) && is_array($data->qty)) {
            return $data->qty;
        }

        $qty = $context['args']['input']['qty'] ?? request()->input('qty');

        return is_array($qty) ? $qty : null;
    }
}
