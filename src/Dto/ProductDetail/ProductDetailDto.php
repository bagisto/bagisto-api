<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class ProductDetailDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The sku.
     */
    public ?string $sku = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The url key.
     */
    public ?string $url_key = null;

    /**
     * The status.
     */
    public ?bool $status = null;

    /**
     * The description.
     */
    public ?string $description = null;

    /**
     * The short description.
     */
    public ?string $short_description = null;

    /**
     * The meta title.
     */
    public ?string $meta_title = null;

    /**
     * The meta description.
     */
    public ?string $meta_description = null;

    /**
     * The meta keywords.
     */
    public ?string $meta_keywords = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The special price.
     */
    public ?float $special_price = null;

    /**
     * The new.
     */
    public ?bool $new = null;

    /**
     * The featured.
     */
    public ?bool $featured = null;

    /**
     * The minimum price.
     */
    public ?float $minimum_price = null;

    /**
     * The maximum price.
     */
    public ?float $maximum_price = null;

    /**
     * The formatted price.
     */
    public ?string $formatted_price = null;

    /**
     * The formatted special price.
     */
    public ?string $formatted_special_price = null;

    /**
     * The formatted minimum price.
     */
    public ?string $formatted_minimum_price = null;

    /**
     * The formatted maximum price.
     */
    public ?string $formatted_maximum_price = null;

    /**
     * The base image url.
     */
    public ?string $base_image_url = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * The is saleable.
     */
    public ?bool $is_saleable = null;

    /** Whether this product is in the authenticated customer's wishlist (current channel): 1 = yes, 0 = no (0 for guests). */
    public ?int $is_in_wishlist = null;

    /** Whether this product is in the authenticated customer's compare list: 1 = yes, 0 = no (0 for guests). */
    public ?int $is_in_compare = null;

    /**
     * Dynamic list of the product's attribute values, resolved from its attribute
     * family (channel/locale-aware) — so any attribute (allow_rma, material, custom,.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $attributes = [];

    /**
     * The color.
     */
    public ?int $color = null;

    /**
     * The size.
     */
    public ?int $size = null;

    /**
     * The brand.
     */
    public ?int $brand = null;

    /** @var CategorySummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $categories = [];

    /** @var ChannelSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $channels = [];

    /**
     * The attribute family.
     */
    #[ApiProperty(readableLink: true)]
    public ?AttributeFamilySummaryDto $attribute_family = null;

    /** @var ProductImageDto[] */
    #[ApiProperty(readableLink: true)]
    public array $images = [];

    /** @var ProductVideoDto[] */
    #[ApiProperty(readableLink: true)]
    public array $videos = [];

    /** @var SuperAttributeDto[] */
    #[ApiProperty(readableLink: true)]
    public array $super_attributes = [];

    /** @var VariantSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $variants = [];

    /** @var BookingProductDto[] */
    #[ApiProperty(readableLink: true)]
    public array $booking_products = [];

    /** @var BundleOptionDto[] */
    #[ApiProperty(readableLink: true)]
    public array $bundle_options = [];

    /** @var GroupedProductDto[] */
    #[ApiProperty(readableLink: true)]
    public array $grouped_products = [];

    /** @var DownloadableLinkDto[] */
    #[ApiProperty(readableLink: true)]
    public array $downloadable_links = [];

    /** @var DownloadableSampleDto[] */
    #[ApiProperty(readableLink: true)]
    public array $downloadable_samples = [];

    /** @var CustomizableOptionDto[] */
    #[ApiProperty(readableLink: true)]
    public array $customizable_options = [];

    /** @var ProductSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $related_products = [];

    /** @var ProductSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $up_sells = [];

    /** @var ProductSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $cross_sells = [];
}
