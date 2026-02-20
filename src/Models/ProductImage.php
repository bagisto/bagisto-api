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
use ApiPlatform\Metadata\Post;
use Webkul\BagistoApi\State\ProductImageProcessor;
use Webkul\BagistoApi\State\ProductImageProvider;
use Webkul\Product\Models\ProductImage as BaseProductImage;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;

#[ApiResource(
    routePrefix: '/api/admin',
    uriTemplate: '/products/{productId}/image',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'image',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(provider: ProductImageProvider::class),
        new Post(processor: ProductImageProcessor::class),
    ],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/admin',
    uriTemplate: '/products/{productId}/images/{id}',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'images',
            identifiers: ['id']
        ),
        'id' => new Link(fromClass: ProductImage::class),
    ],
    operations: [
        new Get(provider: ProductImageProvider::class),
        new Patch(
            provider: ProductImageProvider::class,
            processor: ProductImageProcessor::class
        ),
        new Delete,
    ],
    graphQlOperations: []
)]

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductImages',
    uriTemplate: '/images',
    operations: [
        new GetCollection(provider: ProductImageProvider::class),
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
    shortName: 'ProductImages',
    uriTemplate: '/images/{id}',
    operations: [
        new Get(provider: ProductImageProvider::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new DeleteMutation(name: 'delete'),
    ]
)]
class ProductImage extends BaseProductImage
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
