<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

/**
 * POST /api/admin/carts/{id}/shipping-methods — save the selected shipping
 * method on the draft cart.
 *
 * Mirrors CartController::storeShippingMethod. Sequence: both addresses must
 * be saved first; the guard enforces it with HTTP 409.
 */
class AdminCartSetShippingMethodProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        // Sequence: addresses must be saved before shipping method can be selected.
        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart);

        $code = $this->resolveShippingMethod($data, $context);

        if (empty($code)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.shipping-method-required'));
        }

        Cart::setCart($cart);

        try {
            if (! Cart::saveShippingMethod($code)) {
                return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, __('bagistoapi::app.admin.cart.shipping-method-failed'));
            }

            Cart::collectTotals();

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.shipping-method-saved'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart setShippingMethod failed', [
                'cart_id' => $cart->id,
                'method'  => $code,
                'error'   => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.shipping-method-failed'));
        }
    }

    protected function resolveShippingMethod(mixed $data, array $context): ?string
    {
        if (is_object($data) && ! empty($data->shippingMethod)) {
            return (string) $data->shippingMethod;
        }

        return $context['args']['input']['shippingMethod']
            ?? request()->input('shippingMethod')
            ?? request()->input('shipping_method')
            ?? null;
    }
}
