<?php

namespace Webkul\BagistoApi\Services\Cart;

use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Models\Customer;

class GuestCartMerger
{
    /**
     * Create a new guest cart merger.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
    ) {}

    /**
     * The active guest cart a guest cart token belongs to, or null when it cannot be merged.
     */
    public function findMergeable(?string $guestCartToken, ?int $cartId = null): ?CartModel
    {
        if (! $guestCartToken) {
            return null;
        }

        $guestCart = $this->guestCartTokensRepository->findCartByToken($guestCartToken);

        if (
            ! $guestCart
            || ! $guestCart->is_active
            || $guestCart->customer_id
            || (
                $cartId
                && $cartId !== (int) $guestCart->id
            )
        ) {
            return null;
        }

        return $guestCart;
    }

    /**
     * Merge the guest cart into the customer's active cart, adopting it when the customer has none.
     */
    public function merge(CartModel $guestCart, Customer $customer): CartModel
    {
        $customerCart = $this->cartRepository->findOneWhere([
            'customer_id' => $customer->id,
            'is_active' => 1,
        ]);

        if ($customerCart) {
            $this->mergeIntoCustomerCart($customerCart, $guestCart);
        } else {
            $customerCart = $this->adoptGuestCart($guestCart, $customer);
        }

        return CartModel::find($customerCart->id);
    }

    /**
     * Turn a guest cart into the customer's cart when the customer has none.
     */
    protected function adoptGuestCart(CartModel $guestCart, Customer $customer): CartModel
    {
        $this->cartRepository->update([
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
        ], $guestCart->id);

        $this->guestCartTokensRepository->deleteWhere(['cart_id' => $guestCart->id]);

        $this->forgetSessionCart($guestCart);

        $customerCart = CartModel::find($guestCart->id);

        CartFacade::setCart($customerCart);

        CartFacade::collectTotals();

        return $customerCart;
    }

    /**
     * Add the guest cart's items to the customer's existing cart and drop the guest cart.
     */
    protected function mergeIntoCustomerCart(CartModel $customerCart, CartModel $guestCart): void
    {
        CartFacade::setCart($customerCart);

        foreach ($guestCart->items as $guestCartItem) {
            try {
                CartFacade::addProduct($guestCartItem->product, $guestCartItem->additional ?? []);
            } catch (\Exception) {
                continue;
            }
        }

        CartFacade::collectTotals();

        CartFacade::removeCart($guestCart);
    }

    /**
     * Drop the session's guest cart when it is the cart just merged, so core's login listener does not merge it again.
     */
    protected function forgetSessionCart(CartModel $guestCart): void
    {
        if ((int) session()->get('cart')?->id === (int) $guestCart->id) {
            session()->forget('cart');
        }
    }
}
