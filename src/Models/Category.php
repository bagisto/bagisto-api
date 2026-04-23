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
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Category\Models\Category as BaseCategory;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get,
        new GetCollection(
            paginationEnabled: true,
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: 15,
            paginationMaximumItemsPerPage: 100,
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
