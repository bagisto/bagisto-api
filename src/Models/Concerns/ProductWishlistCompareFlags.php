<?php

namespace Webkul\BagistoApi\Models\Concerns;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\State\ProductRelationFlagResolver;

trait ProductWishlistCompareFlags
{
    public function getIsInWishlistAttribute(): int
    {
        return app(ProductRelationFlagResolver::class)->isInWishlist((int) $this->id) ? 1 : 0;
    }

    /**
     * Whether this product is in the authenticated customer's wishlist (current channel).
     * 1 = in wishlist, 0 = not (always 0 for guests). Returned as 0/1 — not true/"" — so the
     * "not in list" case is an explicit 0 rather than an empty string over GraphQL. Lets clients
     * highlight the wishlist icon per product without cross-referencing the separately-paginated
     * wishlist endpoint. Surfaced as `isInWishlist`.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false,
        schema: ['type' => 'integer', 'enum' => [0, 1]],
    )]
    #[Groups(['product:list'])]
    public function getIs_in_wishlist(): int
    {
        return $this->getIsInWishlistAttribute();
    }

    public function getIsInCompareAttribute(): int
    {
        return app(ProductRelationFlagResolver::class)->isInCompare((int) $this->id) ? 1 : 0;
    }

    /**
     * Whether this product is in the authenticated customer's compare list.
     * 1 = in compare list, 0 = not (always 0 for guests). Compare is not channel-scoped.
     * Surfaced as `isInCompare`.
     */
    #[ApiProperty(
        writable: false,
        readable: true,
        required: false,
        schema: ['type' => 'integer', 'enum' => [0, 1]],
    )]
    #[Groups(['product:list'])]
    public function getIs_in_compare(): int
    {
        return $this->getIsInCompareAttribute();
    }
}
