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

/**
 * POST /api/admin/carts/{id}/coupon — apply a coupon code.
 *
 * Mirrors CartController::storeCoupon status codes:
 *   - 404 unknown / inactive coupon  → ResourceNotFoundException
 *   - 422 already-applied coupon     → ValidationException
 *   - 200 success                    → returns the updated cart
 */
class AdminCartApplyCouponProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $code = $this->resolveCode($data, $context);

        if ($code === null || $code === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.coupon-code-required'));
        }

        Cart::setCart($cart);

        $coupon = app(CartRuleCouponRepository::class)->findOneByField('code', $code);

        if (! $coupon || ! $coupon->cart_rule?->status) {
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
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('AdminCart applyCoupon failed', [
                'cart_id' => $cart->id,
                'code'    => $code,
                'error'   => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, __('bagistoapi::app.admin.cart.coupon-error'));
        }
    }

    protected function resolveCode(mixed $data, array $context): ?string
    {
        if (is_object($data) && ! empty($data->code)) {
            return (string) $data->code;
        }

        $raw = $context['args']['input']['code']
            ?? request()->input('code');

        return $raw !== null ? trim((string) $raw) : null;
    }
}
