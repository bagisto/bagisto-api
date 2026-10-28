<?php

namespace Webkul\BagistoApi\State;

use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Wishlist;

class ProductRelationFlagResolver
{
    /**
     * Customer id the cached sets were built for (null = guest / not yet loaded).
     */
    private ?int $cachedCustomerId = null;

    /**
     * Whether the sets have been loaded for the current cached customer id.
     */
    private bool $loaded = false;

    /**
     * The products in the customer's wishlist.
     *
     * @var array<int, true> product_id => true for the customer's wishlist (current channel)
     */
    private array $wishlistIds = [];

    /**
     * The products in the customer's compare list.
     *
     * @var array<int, true> product_id => true for the customer's compare list
     */
    private array $compareIds = [];

    /**
     * Whether in wishlist.
     */
    public function isInWishlist(int $productId): bool
    {
        $this->ensureLoaded();

        return isset($this->wishlistIds[$productId]);
    }

    /**
     * Whether in compare.
     */
    public function isInCompare(int $productId): bool
    {
        $this->ensureLoaded();

        return isset($this->compareIds[$productId]);
    }

    /**
     * Load (or reload) the membership sets for the currently authenticated customer.
     * Resets when the customer identity changes so the singleton stays correct across.
     */
    private function ensureLoaded(): void
    {
        $customerId = CurrentCustomer::user()?->id;

        if (
            $this->loaded
            && $customerId === $this->cachedCustomerId
        ) {
            return;
        }

        $this->cachedCustomerId = $customerId;
        $this->loaded = true;
        $this->wishlistIds = [];
        $this->compareIds = [];

        if (! $customerId) {
            return;
        }

        $channelId = core()->getCurrentChannel()->id;

        $this->wishlistIds = Wishlist::where('customer_id', $customerId)
            ->where('channel_id', $channelId)
            ->pluck('product_id')
            ->flip()
            ->map(fn () => true)
            ->all();

        $this->compareIds = CompareItem::where('customer_id', $customerId)
            ->pluck('product_id')
            ->flip()
            ->map(fn () => true)
            ->all();
    }
}
