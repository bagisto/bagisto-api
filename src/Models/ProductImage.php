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
use Webkul\BagistoApi\State\ProductImageProvider;
use Webkul\Product\Models\ProductImage as BaseProductImage;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductImages',
    uriTemplate: '/product-images',
    operations: [
        new GetCollection(
            provider: ProductImageProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List product images (root collection)',
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
    shortName: 'ProductImages',
    uriTemplate: '/product-images/{id}',
    operations: [
        new Get(
            provider: ProductImageProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'Get a single product image by ID',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductImages',
    uriTemplate: '/products/{productId}/images',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'images',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            provider: ProductImageProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List images for a product',
                description: 'Returns the image collection for the given product ID.',
            ),
        ),
    ],
    graphQlOperations: []
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
