<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Laravel\Eloquent\Filter\EqualsFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ThemeCustomization',
    operations: [
        new Get(
            uriTemplate: '/theme-customizations/{id}',
            normalizationContext: [
                'skip_null_values' => false,
            ],
        ),
        new GetCollection(
            uriTemplate: '/theme-customizations',
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: 10,
            paginationMaximumItemsPerPage: 100,
            normalizationContext: [
                'skip_null_values' => false,
            ],
            parameters: [
                'type' => new QueryParameter(key: 'type', property: 'type', filter: new EqualsFilter),
            ],
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            parameters: [
                'type' => new QueryParameter(key: 'type', property: 'type', filter: new EqualsFilter),
            ],
        ),
    ],
)]
class ThemeCustomization extends \Webkul\Theme\Models\ThemeCustomization
{
    /**
     * Get unique theme customization identifier for API
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get translation for the current locale
     */
    #[ApiProperty(readable: true, writable: false, readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
    }

    /**
     * Get all translations
     */
    #[ApiProperty(readable: true, writable: false, readableLink: true, description: 'All translations')]
    public function getTranslations()
    {
        return $this->translations;
    }
}
