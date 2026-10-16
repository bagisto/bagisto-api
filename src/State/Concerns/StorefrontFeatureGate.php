<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * Refuses wishlist and compare requests when the store has switched the feature off.
 */
trait StorefrontFeatureGate
{
    protected function assertWishlistEnabled(): void
    {
        if (! core()->getConfigData('customer.settings.wishlist.wishlist_option')) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.wishlist.disabled'));
        }
    }

    protected function assertCompareEnabled(): void
    {
        if (! core()->getConfigData('catalog.products.settings.compare_option')) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.compare-item.disabled'));
        }
    }
}
