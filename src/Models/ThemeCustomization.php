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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
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
            openapi: new Operation(
                tags: ['ThemeCustomization'],
                summary: 'Get a theme customization block by ID',
                description: 'Returns one storefront theme customization block (carousel, static content, etc.) with its current-locale `translation` and all `translations`. The `options` field is a JSON-encoded string. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The theme customization block.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 3,
                                    'themeCode' => 'default',
                                    'channelId' => 1,
                                    'type' => 'category_carousel',
                                    'name' => 'Categories Collections',
                                    'sortOrder' => 3,
                                    'status' => 1,
                                    'createdAt' => '2024-04-16T21:44:15+05:30',
                                    'updatedAt' => '2026-04-07T18:05:39+05:30',
                                    'translation' => [
                                        'id' => 3,
                                        'themeCustomizationId' => 3,
                                        'locale' => 'en',
                                        'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                    ],
                                    'translations' => [
                                        [
                                            'id' => 3,
                                            'themeCustomizationId' => 3,
                                            'locale' => 'en',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                        [
                                            'id' => 29,
                                            'themeCustomizationId' => 3,
                                            'locale' => 'AR',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Theme customization not found.'),
                ],
            ),
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
            openapi: new Operation(
                tags: ['ThemeCustomization'],
                summary: 'List theme customization blocks',
                description: 'Returns the storefront theme customization blocks for the current channel. Filter by `?type=` (e.g. `category_carousel`, `product_carousel`, `static_content`). Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of theme customization blocks.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 3,
                                        'themeCode' => 'default',
                                        'channelId' => 1,
                                        'type' => 'category_carousel',
                                        'name' => 'Categories Collections',
                                        'sortOrder' => 3,
                                        'status' => 1,
                                        'createdAt' => '2024-04-16T21:44:15+05:30',
                                        'updatedAt' => '2026-04-07T18:05:39+05:30',
                                        'translation' => [
                                            'id' => 3,
                                            'themeCustomizationId' => 3,
                                            'locale' => 'en',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                        'translations' => [
                                            [
                                                'id' => 3,
                                                'themeCustomizationId' => 3,
                                                'locale' => 'en',
                                                'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
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
