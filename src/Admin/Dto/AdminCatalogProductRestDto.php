<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCatalogProductRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The sku.
     */
    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?int $status = null;

    /**
     * The price.
     */
    #[ApiProperty(writable: false)]
    public ?string $price = null;

    /**
     * The formatted price.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    /**
     * The special price.
     */
    #[ApiProperty(writable: false)]
    public ?string $special_price = null;

    /**
     * The formatted special price.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_special_price = null;

    /**
     * The special price from.
     */
    #[ApiProperty(writable: false)]
    public ?string $special_price_from = null;

    /**
     * The special price to.
     */
    #[ApiProperty(writable: false)]
    public ?string $special_price_to = null;

    /**
     * The quantity.
     */
    #[ApiProperty(writable: false)]
    public ?int $quantity = null;

    /**
     * The base image url.
     */
    #[ApiProperty(writable: false)]
    public ?string $base_image_url = null;

    /**
     * The images count.
     */
    #[ApiProperty(writable: false)]
    public ?int $images_count = null;

    /**
     * The category id.
     */
    #[ApiProperty(writable: false)]
    public ?int $category_id = null;

    /**
     * The category name.
     */
    #[ApiProperty(writable: false)]
    public ?string $category_name = null;

    /**
     * The channel.
     */
    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    /**
     * The locale.
     */
    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    /**
     * The attribute family id.
     */
    #[ApiProperty(writable: false)]
    public ?int $attribute_family_id = null;

    /**
     * The attribute family name.
     */
    #[ApiProperty(writable: false)]
    public ?string $attribute_family_name = null;

    /**
     * The url key.
     */
    #[ApiProperty(writable: false)]
    public ?string $url_key = null;

    /**
     * The visible individually.
     */
    #[ApiProperty(writable: false)]
    public ?bool $visible_individually = null;

    /**
     * The short description.
     */
    #[ApiProperty(writable: false)]
    public ?string $short_description = null;

    /**
     * The description.
     */
    #[ApiProperty(writable: false)]
    public ?string $description = null;

    /**
     * The meta title.
     */
    #[ApiProperty(writable: false)]
    public ?string $meta_title = null;

    /**
     * The meta description.
     */
    #[ApiProperty(writable: false)]
    public ?string $meta_description = null;

    /**
     * The meta keywords.
     */
    #[ApiProperty(writable: false)]
    public ?string $meta_keywords = null;

    /**
     * The weight.
     */
    #[ApiProperty(writable: false)]
    public ?float $weight = null;

    /**
     * The tax category id.
     */
    #[ApiProperty(writable: false)]
    public ?int $tax_category_id = null;

    /**
     * The manage stock.
     */
    #[ApiProperty(writable: false)]
    public ?bool $manage_stock = null;

    /**
     * The in stock.
     */
    #[ApiProperty(writable: false)]
    public ?bool $in_stock = null;

    /**
     * The featured.
     */
    #[ApiProperty(writable: false)]
    public ?bool $featured = null;

    /**
     * The new.
     */
    #[ApiProperty(writable: false)]
    public ?bool $new = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /**
     * The translations.
     */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /**
     * The images.
     */
    #[ApiProperty(writable: false)]
    public ?array $images = null;

    /**
     * The categories.
     */
    #[ApiProperty(writable: false)]
    public ?array $categories = null;

    /**
     * The inventories.
     */
    #[ApiProperty(writable: false)]
    public ?array $inventories = null;

    /**
     * The customer group prices.
     */
    #[ApiProperty(writable: false)]
    public ?array $customer_group_prices = null;

    /**
     * The super attributes.
     */
    #[ApiProperty(writable: false)]
    public ?array $super_attributes = null;

    /**
     * The variants.
     */
    #[ApiProperty(writable: false)]
    public ?array $variants = null;

    /**
     * The bundle options.
     */
    #[ApiProperty(writable: false)]
    public ?array $bundle_options = null;

    /**
     * The linked products.
     */
    #[ApiProperty(writable: false)]
    public ?array $linked_products = null;

    /**
     * The downloadable links.
     */
    #[ApiProperty(writable: false)]
    public ?array $downloadable_links = null;

    /**
     * The downloadable samples.
     */
    #[ApiProperty(writable: false)]
    public ?array $downloadable_samples = null;

    /**
     * The booking product.
     */
    #[ApiProperty(writable: false)]
    public ?array $booking_product = null;

    /**
     * The customizable options.
     */
    #[ApiProperty(writable: false)]
    public ?array $customizable_options = null;

    /**
     * The videos.
     */
    #[ApiProperty(writable: false)]
    public ?array $videos = null;

    /**
     * The channels.
     */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /**
     * The attributes.
     */
    #[ApiProperty(writable: false)]
    public ?array $attributes = null;

    /**
     * The related products.
     */
    #[ApiProperty(writable: false)]
    public ?array $related_products = null;

    /**
     * The up sells.
     */
    #[ApiProperty(writable: false)]
    public ?array $up_sells = null;

    /**
     * The cross sells.
     */
    #[ApiProperty(writable: false)]
    public ?array $cross_sells = null;

    /**
     * The warnings raised for input the update could not fully apply.
     *
     * @var string[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $warnings = null;
}
