<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\State\CategoryRestProvider;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Category\Models\Category as BaseCategory;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get,
        new GetCollection(
            provider: CategoryRestProvider::class,
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: 15,
            paginationMaximumItemsPerPage: 100,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Category'],
                summary: 'List active categories with optional parent filtering',
                description: 'Returns a flat list of active categories only (status=1). Admin-disabled categories are never returned. Use `?parent_id=N` for direct children of a category. Each item embeds its `translation`, `children`, and `filterableAttributes`. For a hierarchical tree response use /category-trees instead.',
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'parent_id',
                        in: 'query',
                        description: 'Return only direct children of this category ID. Accepts `parentId` as an alias.',
                        required: false,
                        schema: ['type' => 'integer', 'example' => 2],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'page',
                        in: 'query',
                        description: 'Page number (1-based).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 1],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'per_page',
                        in: 'query',
                        description: 'Items per page. Default 15, max 100.',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 15],
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
                    'type'        => 'Int',
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
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->translation;
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
     * Get children categories
     */
    #[ApiProperty(readableLink: true, description: 'Child categories')]
    public function getChildren()
    {
        return $this->children;
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
