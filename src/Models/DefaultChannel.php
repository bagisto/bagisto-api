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
    public ?string $logoUrl = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $faviconUrl = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $timezone = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $isMaintenanceOn = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $allowedIps = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $rootCategoryId = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $defaultLocaleId = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $baseCurrencyId = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $updatedAt = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $maintenanceModeText = null;

    // Nested relationships
    #[ApiProperty(writable: false, readable: true)]
    public ?object $defaultLocale = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $baseCurrency = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $locales = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $currencies = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $inventorySources = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $rootCategory = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?array $translations = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?object $homeSeo = null;

    /**
     * API Platform identifier
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
