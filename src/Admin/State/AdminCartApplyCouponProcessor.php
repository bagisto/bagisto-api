<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Exception\ValidationException;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;

class AdminCartApplyCouponProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $code = $this->resolveCode($data, $context);

        if (
            $code === null
            || $code === ''
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.coupon-code-required'));
        }

        Cart::setCart($cart);

        $coupon = app(CartRuleCouponRepository::class)->findOneByField('code', $code);

        if (
            ! $coupon
            || ! $coupon->cart_rule?->status
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.coupon-not-found'));
        }

        if (Cart::getCart()?->coupon_code === $coupon->code) {
            throw new ValidationException(__('bagistoapi::app.admin.cart.coupon-already-applied'));
        }

        try {
            Cart::setCouponCode($coupon->code)->collectTotals();

            if (Cart::getCart()?->coupon_code === $coupon->code) {
                return AdminCartPresenter::present(Cart::getCart(), true, __('bagistoapi::app.admin.cart.coupon-applied'));
            }

            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.coupon-not-found'));
        } catch (ResourceNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('AdminCart applyCoupon failed', [
                'cart_id' => $cart->id,
                'code' => $code,
                'error' => $exception->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, __('bagistoapi::app.admin.cart.coupon-error'));
        }
    }

    /**
     * Resolve code.
     */
    protected function resolveCode(mixed $data, array $context): ?string
    {
        if (
            is_object($data)
            && ! empty($data->code)
        ) {
            return (string) $data->code;
        }

        $raw = $context['args']['input']['code']
            ?? request()->input('code');

        return $raw !== null ? trim((string) $raw) : null;
    }
}
