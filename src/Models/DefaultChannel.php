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
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    /**
     * The code.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $description = null;

    /**
     * The theme.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $theme = null;

    /**
     * The hostname.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $hostname = null;

    /**
     * The logo url.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $logo_url = null;

    /**
     * The favicon url.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $favicon_url = null;

    /**
     * The timezone.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $timezone = null;

    /**
     * The is maintenance on.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $is_maintenance_on = null;

    /**
     * The root category id.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $root_category_id = null;

    /**
     * The default locale id.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $default_locale_id = null;

    /**
     * The base currency id.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $base_currency_id = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $updated_at = null;

    /**
     * The maintenance mode text.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $maintenance_mode_text = null;

    // Nested relationships
    /**
     * The default locale.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $default_locale = null;

    /**
     * The base currency.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $base_currency = null;

    /**
     * The locales.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $locales = null;

    /**
     * The currencies.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $currencies = null;

    /**
     * The inventory sources.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $inventory_sources = null;

    /**
     * The root category.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $root_category = null;

    /**
     * The translations.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $translations = null;

    /**
     * The home seo.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $home_seo = null;

    /**
     * API Platform identifier.
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
