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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\Traits\ServesLoadedTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Section',
    operations: [
        new Get(
            uriTemplate: '/sections/{id}',
            normalizationContext: [
                'skip_null_values' => false,
            ],
            openapi: new Operation(
                tags: ['Theme'],
                summary: 'Get a storefront section by ID',
                description: 'Returns one published section (carousel, static content, footer links, etc.) of the current channel\'s active theme, with its current-locale `translation` and all `translations`. The `options` field is a JSON-encoded string. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The section.',
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
                                        'sectionId' => 3,
                                        'locale' => 'en',
                                        'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                    ],
                                    'translations' => [
                                        [
                                            'id' => 3,
                                            'sectionId' => 3,
                                            'locale' => 'en',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                        [
                                            'id' => 29,
                                            'sectionId' => 3,
                                            'locale' => 'ar',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Section not found, unpublished, or not part of this channel\'s active theme.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/sections',
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: 50,
            paginationMaximumItemsPerPage: 100,
            normalizationContext: [
                'skip_null_values' => false,
            ],
            openapi: new Operation(
                tags: ['Theme'],
                summary: 'List storefront sections',
                description: 'Returns the published sections of the current channel\'s active theme, in render order. A channel holds one page worth of sections, so the default page size is 50 — enough to draw the storefront in a single call. Filter by `?type=` (`image_carousel`, `product_carousel`, `category_carousel`, `footer_links`, `static_content`, `services_content`). Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of sections.',
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
                                            'sectionId' => 3,
                                            'locale' => 'en',
                                            'options' => '{"filters": {"sort": "asc", "limit": "10", "parent_id": "1"}}',
                                        ],
                                        'translations' => [
                                            [
                                                'id' => 3,
                                                'sectionId' => 3,
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
class Section extends \Webkul\Theme\Models\Section
{
    use ServesLoadedTranslation;

    /**
     * @var list<string>
     */
    protected $with = ['translations'];

    /**
     * Staged edits belong to the appearance editor, not to the storefront.
     *
     * @var list<string>
     */
    protected $hidden = [
        'draft_status',
        'draft_sort_order',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('bagisto_api_storefront', function (Builder $builder) {
            $channel = core()->getCurrentChannel();

            $builder
                ->where('theme_sections.status', 1)
                ->where('theme_sections.channel_id', $channel->id)
                ->where('theme_sections.theme_code', $channel->theme ?: config('themes.shop-default'))
                ->orderBy('theme_sections.sort_order');
        });
    }

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(readable: true, writable: false, readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
    }

    #[ApiProperty(readable: true, writable: false, readableLink: true, description: 'All translations')]
    public function getTranslations()
    {
        return $this->translations;
    }
}
