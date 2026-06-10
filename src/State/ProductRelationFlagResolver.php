<?php

namespace Webkul\BagistoApi\State;

use Illuminate\Support\Facades\Auth;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Wishlist;

/**
 * Resolves the authenticated customer's relation flags for products — currently
 * whether a product sits in their wishlist or compare list, extensible to future
 * per-customer relations (e.g. recently viewed).
 *
 * Registered as a request-scoped singleton so the membership sets are loaded once
 * per request (one query per list, lazily on first access) and reused across every
 * product in a paginated response — no N+1.
 *
 * Memoization is keyed by the resolved customer id: if the authenticated customer
 * changes mid-process (e.g. an authenticated request followed by a guest request in
 * the same test run) the cached sets are reset and reloaded. This mirrors the
 * AdminApiGuard re-resolution fix — never memoize across requests without an
 * identity-stable cache key.
 */
class ProductRelationFlagResolver
{
    /** Customer id the cached sets were built for (null = guest / not yet loaded). */
    private ?int $cachedCustomerId = null;

    /** Whether the sets have been loaded for the current cached customer id. */
    private bool $loaded = false;

    /** @var array<int, true> product_id => true for the customer's wishlist (current channel) */
    private array $wishlistIds = [];

    /** @var array<int, true> product_id => true for the customer's compare list */
    private array $compareIds = [];

    public function isInWishlist(int $productId): bool
    {
        $this->ensureLoaded();

        return isset($this->wishlistIds[$productId]);
    }

    public function isInCompare(int $productId): bool
    {
        $this->ensureLoaded();

        return isset($this->compareIds[$productId]);
    }

    /**
     * Load (or reload) the membership sets for the currently authenticated customer.
     * Resets when the customer identity changes so the singleton stays correct across
     * multiple requests in one process.
     */
    private function ensureLoaded(): void
    {
        $customerId = Auth::guard('sanctum')->user()?->id;

        if ($this->loaded && $customerId === $this->cachedCustomerId) {
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
