<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

/**
 * POST /api/admin/carts/{id}/payment-methods — save the selected payment
 * method on the draft cart.
 *
 * Mirrors CartController::storePaymentMethod. Sequence: items + addresses +
 * shipping method must already be set, otherwise HTTP 409.
 */
class AdminCartSetPaymentMethodProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart);
        AdminCartSequenceGuard::requireShippingMethod($cart);

        $method = $this->resolvePaymentMethod($data, $context);

        if (empty($method)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.payment-method-required'));
        }

        Cart::setCart($cart);

        try {
            if (! Cart::savePaymentMethod(['method' => $method])) {
                return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, __('bagistoapi::app.admin.cart.payment-method-failed'));
            }

            Cart::collectTotals();

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.payment-method-saved'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart setPaymentMethod failed', [
                'cart_id' => $cart->id,
                'method'  => $method,
                'error'   => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.payment-method-failed'));
        }
    }

    protected function resolvePaymentMethod(mixed $data, array $context): ?string
    {
        if (is_object($data) && ! empty($data->method)) {
            return (string) $data->method;
        }

        return $context['args']['input']['method']
            ?? request()->input('method')
            ?? request()->input('payment')
            ?? null;
    }
}
