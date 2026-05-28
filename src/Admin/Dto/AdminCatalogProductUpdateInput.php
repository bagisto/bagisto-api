<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for Admin Catalog Product update (Phase 5.9).
 *
 * Pass-through shape: declares the most common scalar fields so GraphQL has
 * a typed schema surface, plus an `extras` catch-all for custom-attribute
 * values that vary by family. The processor merges every set property +
 * `extras` into a single payload and forwards to ProductRepository::update.
 *
 * Type-specific blocks (superAttributes/variants, bundleOptions, links,
 * downloadableLinks/downloadableSamples, booking) pass through as-is to the
 * type instance's update().
 *
 * Sub-resource fields (images, inventories, customer_group_prices) are
 * silently stripped — they have dedicated endpoints (5.11/5.12/5.13). The
 * response includes a `_warnings` array noting which fields were dropped.
 *
 * REST   : PUT /api/admin/catalog/products/{id}
 * GraphQL: updateAdminCatalogProduct
 */
class AdminCatalogProductUpdateInput
{
    /**
     * Resource IRI — required for GraphQL delete/update routing. API Platform
     * populates this automatically when the mutation declares `id: ID!`.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $sku = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $urlKey = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $visibleIndividually = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $guestCheckout = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $new = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $featured = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $price = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPrice = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPriceFrom = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPriceTo = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $cost = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $weight = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $taxCategoryId = null;

    /** @var int[]|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $categories = null;

    /** @var int[]|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /** @var array<string, mixed>|null  Locale-keyed translation map */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $translations = null;

    /** @var array<string, int[]>|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $superAttributes = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $variants = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $bundleOptions = null;

    /** @var array<int, mixed>|null  Grouped product links */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $links = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $downloadableLinks = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $downloadableSamples = null;

    /** @var array<string, mixed>|null  Booking sub-type payload */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $booking = null;

    /**
     * Catch-all for custom-attribute values (any other field accepted by
     * ProductRepository::update). Merged into the payload alongside the typed
     * fields above. Use this for attributes the family defines but this DTO
     * doesn't enumerate (e.g. `material`, `brand`, etc.).
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $extras = null;
}
