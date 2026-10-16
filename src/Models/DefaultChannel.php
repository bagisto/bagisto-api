<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\DefaultChannelProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DefaultChannel',
    operations: [],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            provider: DefaultChannelProvider::class,
        ),
    ],
    normalizationContext: [
        'skip_null_values' => false,
    ],
)]
class DefaultChannel
{
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $code = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $name = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $description = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $theme = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $hostname = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $logo_url = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $favicon_url = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $timezone = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $is_maintenance_on = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $root_category_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $default_locale_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $base_currency_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $maintenance_mode_text = null;

    // Nested relationships
    #[ApiProperty(writable: false, readable: true)]
    public ?object $default_locale = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $base_currency = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $locales = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $currencies = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $inventory_sources = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $root_category = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $translations = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $home_seo = null;

    /**
     * API Platform identifier
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
