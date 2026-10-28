<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

class AdminCartSetPaymentMethodProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
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
        } catch (\Throwable $exception) {
            Log::warning('AdminCart setPaymentMethod failed', [
                'cart_id' => $cart->id,
                'method' => $method,
                'error' => $exception->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $exception->getMessage() ?: __('bagistoapi::app.admin.cart.payment-method-failed'));
        }
    }

    /**
     * Resolve payment method.
     */
    protected function resolvePaymentMethod(mixed $data, array $context): ?string
    {
        if (
            is_object($data)
            && ! empty($data->method)
        ) {
            return (string) $data->method;
        }

        return $context['args']['input']['method']
            ?? request()->input('method')
            ?? request()->input('payment')
            ?? null;
    }
}
