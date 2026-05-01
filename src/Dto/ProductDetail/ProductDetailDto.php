<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class ProductDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $sku = null;

    public ?string $type = null;

    public ?string $name = null;

    public ?string $url_key = null;

    public ?bool $status = null;

    public ?string $description = null;

    public ?string $short_description = null;

    public ?float $price = null;

    public ?float $special_price = null;

    public ?bool $new = null;

    public ?bool $featured = null;

    public ?float $minimum_price = null;

    public ?float $maximum_price = null;

    public ?string $formatted_price = null;

    public ?string $formatted_special_price = null;

    public ?string $formatted_minimum_price = null;

    public ?string $formatted_maximum_price = null;

    public ?string $base_image_url = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?bool $is_saleable = null;

    public ?int $color = null;

    public ?int $size = null;

    public ?int $brand = null;

    /** @var CategorySummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $categories = [];

    /** @var ChannelSummaryDto[] */
    #[ApiProperty(readableLink: true)]
    public array $channels = [];

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
