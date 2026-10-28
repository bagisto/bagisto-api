<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Models\GuestCartTokens;

trait ResolvesCartToken
{
    /**
     * Resolve the guest cart token issued for this cart. Null for a customer-owned cart,
     * which is addressed by the customer's bearer token instead.
     */
    protected function resolveCartToken($cart): ?string
    {
        if (empty($cart?->id)) {
            return null;
        }

        return GuestCartTokens::where('cart_id', $cart->id)->orderByDesc('id')->value('token');
    }
}
