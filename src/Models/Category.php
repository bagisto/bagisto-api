<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\Category\Models\Category as BaseCategory;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get,
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 15,
            paginationMaximumItemsPerPage: 100,
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection,
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
     * Get category translation
     */
    #[ApiProperty(readableLink: true)]
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

    #[ApiProperty(readableLink: true)]
    public function getChildren()
    {
        return $this->children;
    }
}
