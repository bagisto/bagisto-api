<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminCatalogProduct detail + listing. Flat shape — every
 * nested block is a plain array/object (the historical REST payload). Snake_case
 * props surface as camelCase via the central output converter; the providers
 * write camelCase and AcceptsCamelCaseWrites maps them onto the snake props.
 * Over GraphQL the same data is served as field-selectable connections / typed
 * objects off the AdminCatalogProduct Eloquent resource (no output: DTO there).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCatalogProductRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?string $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    #[ApiProperty(writable: false)]
    public ?string $special_price = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_special_price = null;

    #[ApiProperty(writable: false)]
    public ?string $special_price_from = null;

    #[ApiProperty(writable: false)]
    public ?string $special_price_to = null;

    #[ApiProperty(writable: false)]
    public ?int $quantity = null;

    #[ApiProperty(writable: false)]
    public ?string $base_image_url = null;

    #[ApiProperty(writable: false)]
    public ?int $images_count = null;

    #[ApiProperty(writable: false)]
    public ?int $category_id = null;

    #[ApiProperty(writable: false)]
    public ?string $category_name = null;

    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?int $attribute_family_id = null;

    #[ApiProperty(writable: false)]
    public ?string $attribute_family_name = null;

    #[ApiProperty(writable: false)]
    public ?string $url_key = null;

    #[ApiProperty(writable: false)]
    public ?bool $visible_individually = null;

    #[ApiProperty(writable: false)]
    public ?string $short_description = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $meta_title = null;

    #[ApiProperty(writable: false)]
    public ?string $meta_description = null;

    #[ApiProperty(writable: false)]
    public ?string $meta_keywords = null;

    #[ApiProperty(writable: false)]
    public ?float $weight = null;

    #[ApiProperty(writable: false)]
    public ?int $tax_category_id = null;

    #[ApiProperty(writable: false)]
    public ?bool $manage_stock = null;

    #[ApiProperty(writable: false)]
    public ?bool $in_stock = null;

    #[ApiProperty(writable: false)]
    public ?bool $featured = null;

    #[ApiProperty(writable: false)]
    public ?bool $new = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    #[ApiProperty(writable: false)]
    public ?array $images = null;

    #[ApiProperty(writable: false)]
    public ?array $categories = null;

    #[ApiProperty(writable: false)]
    public ?array $inventories = null;

    #[ApiProperty(writable: false)]
    public ?array $customer_group_prices = null;

    #[ApiProperty(writable: false)]
    public ?array $super_attributes = null;

    #[ApiProperty(writable: false)]
    public ?array $variants = null;

    #[ApiProperty(writable: false)]
    public ?array $bundle_options = null;

    #[ApiProperty(writable: false)]
    public ?array $linked_products = null;

    #[ApiProperty(writable: false)]
    public ?array $downloadable_links = null;

    #[ApiProperty(writable: false)]
    public ?array $downloadable_samples = null;

    #[ApiProperty(writable: false)]
    public ?array $booking_product = null;

    #[ApiProperty(writable: false)]
    public ?array $customizable_options = null;

    #[ApiProperty(writable: false)]
    public ?array $videos = null;

    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    #[ApiProperty(writable: false)]
    public ?array $attributes = null;

    #[ApiProperty(writable: false)]
    public ?array $related_products = null;

    #[ApiProperty(writable: false)]
    public ?array $up_sells = null;

    #[ApiProperty(writable: false)]
    public ?array $cross_sells = null;

    /** @var string[]|null */
    #[ApiProperty(writable: false)]
    public ?array $warnings = null;
}
