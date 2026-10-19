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
     * ProductRepository::update). Merged into the payload alongside the typed.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $extras = null;
}
