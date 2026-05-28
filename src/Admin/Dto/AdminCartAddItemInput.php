<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/items and the GraphQL
 * createAdminCartAddItem mutation.
 *
 * Mirrors the shop add-to-cart shape so every product type works: simple,
 * configurable (selectedConfigurableOption + superAttribute), bundle
 * (bundleOptions / bundle_option_qty), grouped (qty[]), downloadable
 * (links / downloadable_link_ids).
 *
 * The processor reads from this DTO when available but always falls back to
 * request()->all() so any storefront-style extra keys are forwarded to
 * Cart::addProduct() without us having to enumerate them.
 */
class AdminCartAddItemInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[Groups(['mutation'])]
    public ?int $quantity = null;
}
