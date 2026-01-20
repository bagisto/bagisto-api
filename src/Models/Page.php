<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Laravel\Eloquent\Filter\SearchFilter;
use Webkul\CMS\Models\Page as BasePage;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'page',
    operations: [
        new Get,
        new GetCollection,
    ],
    graphQlOperations: [
        new Query(),
        new QueryCollection(),
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
#[QueryParameter(key: 'url_key', filter: SearchFilter::class)]
class Page extends BasePage
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
