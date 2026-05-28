<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

/**
 * DELETE /api/admin/carts/{id}/items (body: { cartItemId }) — remove a single
 * line-item from the draft cart. Mirrors CartController::destroyItem.
 */
class AdminCartRemoveItemProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $itemId = $this->resolveCartItemId($data, $context);

        if (! $itemId) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.cart-item-required'));
        }

        Cart::setCart($cart);

        try {
            Cart::removeItem($itemId);
            Cart::collectTotals();

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.item-removed'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart removeItem failed', [
                'cart_id'      => $cart->id,
                'cart_item_id' => $itemId,
                'error'        => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.item-remove-failed'));
        }
    }

    protected function resolveCartItemId(mixed $data, array $context): ?int
    {
        if (is_object($data) && ! empty($data->cartItemId)) {
            return (int) $data->cartItemId;
        }

        $raw = $context['args']['input']['cartItemId']
            ?? request()->input('cartItemId')
            ?? request()->input('cart_item_id');

        return $raw ? (int) $raw : null;
    }
}
