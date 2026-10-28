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
use Webkul\BagistoApi\State\ProductImageProvider;
use Webkul\Product\Models\ProductImage as BaseProductImage;
use Webkul\Product\Models\ProductImageTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductImages',
    uriTemplate: '/product-images',
    operations: [
        new GetCollection(
            provider: ProductImageProvider::class,
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List product images (root collection)',
                description: 'Public endpoint. Returns all product images across the store.',
                responses: [
                    '200' => new Response(
                        description: 'Product image collection',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 967,
                                        'type' => 'images',
                                        'path' => 'product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                        'productId' => 1,
                                        'position' => 1,
                                        'publicPath' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                        'altText' => 'Blue running shoe, side view',
                                        'fileName' => 'zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT',
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
    shortName: 'ProductImages',
    uriTemplate: '/product-images/{id}',
    operations: [
        new Get(
            provider: ProductImageProvider::class,
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Get a single product image by ID',
                description: 'Public endpoint. Returns a single product image.',
                responses: [
                    '200' => new Response(
                        description: 'Product image',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 967,
                                    'type' => 'images',
                                    'path' => 'product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                    'productId' => 1,
                                    'position' => 1,
                                    'publicPath' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                    'altText' => 'Blue running shoe, side view',
                                    'fileName' => 'zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Product image not found.',
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
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List images for a product',
                description: 'Returns the image collection for the given product ID.',
                responses: [
                    '200' => new Response(
                        description: 'Product image collection',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 967,
                                        'type' => 'images',
                                        'path' => 'product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                        'productId' => 1,
                                        'position' => 1,
                                        'publicPath' => 'http://localhost:8000/storage/product/1/zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT.webp',
                                        'altText' => 'Blue running shoe, side view',
                                        'fileName' => 'zKcWZTLDjcawJmaNg8g1cpARqwVONgEKEflabstT',
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
class ProductImage extends BaseProductImage
{
    /**
     * Astrotomic derives the translation model from the class name, which for this
     * subclass would be a ProductImageTranslation in this namespace that does not exist.
     */
    public $translationModel = ProductImageTranslation::class;

    protected $visible = [
        'id',
        'type',
        'path',
        'product_id',
        'position',
        'public_path',
        'alt_text',
        'file_name',
    ];

    #[ApiProperty(readable: true, writable: false)]
    public function getPublicPathAttribute(): ?string
    {
        return $this->getUrlAttribute();
    }

    /**
     * The alt text of the image, for the current locale.
     *
     * `alt_text` lives on the translation, and a property that is neither a column nor an
     * accessor is not part of the resource — so this exists to put it there. Translatable
     * has already resolved the value by the time the accessor runs, which is why it is
     * handed straight back rather than looked up again.
     */
    #[ApiProperty(readable: true, writable: false, description: 'Description of the image for the current locale.')]
    public function getAltTextAttribute(?string $value = null): ?string
    {
        return $value;
    }

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
