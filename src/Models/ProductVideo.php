<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use Webkul\Product\Models\ProductVideo as BaseProductVideo;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/products/{productId}/video',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'video',
            identifiers: ['id']
        ),
    ],
    operations: [],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/admin',
    uriTemplate: '/products/{productId}/videos/{id}',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'videos',
            identifiers: ['id']
        ),
        'id' => new Link(fromClass: ProductVideo::class),
    ],
    operations: [
        new Get,
        new Patch,
        new Delete,
    ],
    graphQlOperations: []
)]

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductVideos',
    uriTemplate: '/videos',
    operations: [
        new GetCollection,
    ],
    graphQlOperations: [
        new QueryCollection(
            args: [
                'product_id' => ['type' => 'Int', 'description' => 'Filter by product ID'],
            ]
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'ProductVideos',
    uriTemplate: '/videos/{id}',
    operations: [
        new Get,
    ],
    graphQlOperations: [
        new Query,
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new DeleteMutation(name: 'delete'),
    ]
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
