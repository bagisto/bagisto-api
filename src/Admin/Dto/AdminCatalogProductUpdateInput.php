<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductUpdateInput
{
    /**
     * Resource IRI — required for GraphQL delete/update routing. API Platform
     * populates this automatically when the mutation declares `id: ID!`.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The sku.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $sku = null;

    /**
     * The url key.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $urlKey = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The visible individually.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $visibleIndividually = null;

    /**
     * The guest checkout.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $guestCheckout = null;

    /**
     * The new.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $new = null;

    /**
     * The featured.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $featured = null;

    /**
     * The price.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $price = null;

    /**
     * The special price.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPrice = null;

    /**
     * The special price from.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPriceFrom = null;

    /**
     * The special price to.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $specialPriceTo = null;

    /**
     * The cost.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $cost = null;

    /**
     * The weight.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $weight = null;

    /**
     * The tax category id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $taxCategoryId = null;

    /**
     * The ids of the categories the product is assigned to.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $categories = null;

    /**
     * The ids of the channels the product is available in.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * The per-locale attribute values of the product.
     *
     * @var array<string, mixed>|null Locale-keyed translation map
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $translations = null;

    /**
     * The configurable attribute codes mapped to their selected option ids.
     *
     * @var array<string, int[]>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $superAttributes = null;

    /**
     * The variants of a configurable product.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $variants = null;

    /**
     * The options of a bundle product and their products.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $bundleOptions = null;

    /**
     * The products linked to a grouped product.
     *
     * @var array<int, mixed>|null Grouped product links
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $links = null;

    /**
     * The links of a downloadable product.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $downloadableLinks = null;

    /**
     * The samples of a downloadable product.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $downloadableSamples = null;

    /**
     * The booking settings of a booking product.
     *
     * @var array<string, mixed>|null Booking sub-type payload
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $booking = null;

    /**
     * Catch-all for custom-attribute values (any other field accepted by
     * ProductRepository::update). Merged into the payload alongside the typed.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $extras = null;
}
