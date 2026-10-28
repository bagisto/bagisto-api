<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

class AdminCartRemoveItemProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
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
        } catch (\Throwable $exception) {
            Log::warning('AdminCart removeItem failed', [
                'cart_id' => $cart->id,
                'cart_item_id' => $itemId,
                'error' => $exception->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $exception->getMessage() ?: __('bagistoapi::app.admin.cart.item-remove-failed'));
        }
    }

    /**
     * Resolve cart item id.
     */
    protected function resolveCartItemId(mixed $data, array $context): ?int
    {
        if (
            is_object($data)
            && ! empty($data->cartItemId)
        ) {
            return (int) $data->cartItemId;
        }

        $raw = $context['args']['input']['cartItemId']
            ?? request()->input('cartItemId')
            ?? request()->input('cart_item_id');

        return $raw ? (int) $raw : null;
    }
}
