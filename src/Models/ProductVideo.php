<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\Product\Models\ProductVideo as BaseProductVideo;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductVideos',
    uriTemplate: '/product-videos',
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List product videos (root collection)',
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: \Webkul\BagistoApi\State\CursorAwareCollectionProvider::class,
            args: [
                'product_id' => ['type' => 'Int', 'description' => 'Filter by product ID'],
            ]
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductVideos',
    uriTemplate: '/product-videos/{id}',
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'Get a single product video by ID',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductVideos',
    uriTemplate: '/products/{productId}/videos',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'videos',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List videos for a product',
                description: 'Returns the video collection for the given product ID.',
            ),
        ),
    ],
    graphQlOperations: []
)]
class ProductVideo extends BaseProductVideo
{
    protected $visible = [
        'id',
        'type',
        'path',
        'product_id',
        'position',
        'public_path',
    ];

    #[ApiProperty(readable: true, writable: false)]
    public function getPublicPathAttribute(): ?string
    {
        return env('API_URL').$this->getUrlAttribute();
    }

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
