<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\State\PageProvider;
use Webkul\CMS\Models\Page as BasePage;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'page',
    operations: [
        new Get(
            provider: PageProvider::class,
        ),
        new GetCollection(
            provider: PageProvider::class,
        ),
    ],
    graphQlOperations: [
        new Query(resolver: \Webkul\BagistoApi\Resolver\BaseQueryItemResolver::class),
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
        new QueryCollection(
            name: 'pageByUrlKey',
            args: [
                'urlKey' => [
                    'type'        => 'String!',
                    'description' => 'The URL key of the page',
                ],
            ],
            paginationEnabled: false,
            resolver: PageByUrlKeyResolver::class,
        ),
    ],
)]
class Page extends BasePage
{
    /**
     * Get unique page identifier for API Platform
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Get layout
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * Get created at
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    /**
     * Get updated at
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    /**
     * Get current locale translation for API
     */
    #[ApiProperty(readable: true, writable: false, description: 'Current locale translation')]
    public function getCurrentTranslation(): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->translations->firstWhere('locale', app()->getLocale())
            ?? $this->translations->first();
    }
}
