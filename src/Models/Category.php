<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\State\CategoryRestProvider;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Category\Models\Category as BaseCategory;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Category'],
                summary: 'Get a single active category',
                description: 'Returns one active category by ID. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Category detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 24,
                                    'position' => 1,
                                    'status' => 1,
                                    'displayMode' => 'products_and_description',
                                    '_lft' => 27,
                                    '_rgt' => 32,
                                    'createdAt' => '2026-05-21T12:53:40+05:30',
                                    'updatedAt' => '2026-05-21T12:53:40+05:30',
                                    'url' => '',
                                    'filterableAttributes' => [],
                                    'translations' => [],
                                    'children' => ['/api/shop/categories/25', '/api/shop/categories/26'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Category not found.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            provider: CategoryRestProvider::class,
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: 10,
            paginationMaximumItemsPerPage: 50,
            openapi: new Operation(
                tags: ['Category'],
                summary: 'List active categories with optional parent filtering',
                description: 'Returns a flat list of active categories only (status=1). Admin-disabled categories are never returned. Use `?parent_id=N` for direct children of a category. Each item embeds its `translation`, `children`, and `filterableAttributes`. For a hierarchical tree response use /category-trees instead. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of active categories.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 24,
                                        'position' => 1,
                                        'status' => 1,
                                        'displayMode' => 'products_and_description',
                                        '_lft' => 27,
                                        '_rgt' => 32,
                                        'createdAt' => '2026-05-21T12:53:40+05:30',
                                        'updatedAt' => '2026-05-21T12:53:40+05:30',
                                        'url' => '',
                                        'filterableAttributes' => [],
                                        'translations' => [],
                                        'children' => ['/api/shop/categories/25', '/api/shop/categories/26'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new Parameter(
                        name: 'parent_id',
                        in: 'query',
                        description: 'Return only direct children of this category ID. Accepts `parentId` as an alias.',
                        required: false,
                        schema: ['type' => 'integer', 'example' => 2],
                    ),
                    new Parameter(
                        name: 'page',
                        in: 'query',
                        description: 'Page number (1-based).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 1],
                    ),
                    new Parameter(
                        name: 'per_page',
                        in: 'query',
                        description: 'Items per page. Default 10, max 50.',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 10],
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
        new QueryCollection(
            name: 'tree',
            args: [
                'parentId' => [
                    'type' => 'Int',
                    'description' => 'Only children of this category will be returned, usually a root category.',
                ],
            ],
            paginationEnabled: false,
            resolver: CategoryCollectionResolver::class
        ),
    ],
)]
class Category extends BaseCategory
{
    /**
     * Get category translation for the current locale
     */
    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
    }

    /**
     * Override core Category::getUrlAttribute() — when the translated slug is
     * null (no translation row, common for newly-created categories or admin
     * locales other than the default), core's `url($null)` returns the
     * UrlGenerator object instead of a string. Symfony Serializer then tries to
     * normalize that UrlGenerator → reaches Request::getSession() → throws
     * SessionNotFoundException on the stateless API.
     *
     * Always return a string (or empty string) — never an object.
     */
    public function getUrlAttribute(): string
    {
        try {
            $slug = $this->translate(core()->getCurrentLocale()->code)?->slug
                ?? $this->translate(core()->getDefaultLocaleCodeFromDefaultChannel())?->slug;

            return $slug ? (string) url($slug) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Unique category identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get children categories — IDs only (no nested objects).
     *
     * Returning a deep object tree here forces Symfony Serializer to recurse
     * through every descendant + its filterableAttributes + their options +
     * translations, which on a real catalogue (e.g. the root category) explodes
     * to thousands of queries and hits the PHP max_execution_time.
     * Clients that need a nested tree should use /api/shop/category-trees,
     * which builds the structure in a single bounded-depth provider pass.
     */
    #[ApiProperty(readableLink: false, description: 'Direct child category IDs (use /category-trees for the full nested tree)')]
    public function getChildren(): array
    {
        try {
            return $this->children()->pluck('id')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Pivot relation (snake_case name so Eloquent's __get matches the normalized
     * property path). The GraphQL `filterableAttributes` field is actually resolved
     * by ProductRelationResolverFactory, which short-circuits the default Attribute
     * collection provider and scopes results to the category_filterable_attributes
     * pivot.
     */
    #[ApiProperty(readableLink: true, description: 'Filterable attributes assigned to this category')]
    public function filterable_attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_filterable_attributes')
            ->with([
                'options' => fn ($q) => $q->orderBy('sort_order'),
                'translations',
                'options.translations',
            ]);
    }
}
