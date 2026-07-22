<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Product\Models\ProductVideo as BaseProductVideo;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductVideos',
    uriTemplate: '/product-videos',
    operations: [
        new GetCollection(
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List product videos (root collection)',
                description: 'Public endpoint. Returns all product videos across the store.',
                responses: [
                    '200' => new Response(
                        description: 'Product video collection',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 12,
                                        'type' => 'videos',
                                        'path' => 'product/5/demo-clip.mp4',
                                        'productId' => 5,
                                        'position' => 1,
                                        'publicPath' => 'http://localhost:8000/storage/product/5/demo-clip.mp4',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
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
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Get a single product video by ID',
                description: 'Public endpoint. Returns a single product video.',
                responses: [
                    '200' => new Response(
                        description: 'Product video',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'type' => 'videos',
                                    'path' => 'product/5/demo-clip.mp4',
                                    'productId' => 5,
                                    'position' => 1,
                                    'publicPath' => 'http://localhost:8000/storage/product/5/demo-clip.mp4',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Product video not found.',
                    ),
                ],
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
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List videos for a product',
                description: 'Returns the video collection for the given product ID.',
                responses: [
                    '200' => new Response(
                        description: 'Product video collection',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 12,
                                        'type' => 'videos',
                                        'path' => 'product/5/demo-clip.mp4',
                                        'productId' => 5,
                                        'position' => 1,
                                        'publicPath' => 'http://localhost:8000/storage/product/5/demo-clip.mp4',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
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
        return $this->getUrlAttribute();
    }

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
