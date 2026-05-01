<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\GroupedProductsProvider;
use Webkul\Product\Models\ProductGroupedProduct as BaseProductGroupedProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'Get a grouped-product member',
                description: 'A ProductGroupedProduct row links a child product into a grouped-type parent. Used when displaying the components of a grouped-type product.',
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: GroupedProductsProvider::class,
            links: [
                new Link(
                    fromProperty: 'groupedProducts',
                    fromClass: Product::class,
                    toClass: self::class,
                    identifiers: ['product_id'],
                ),
            ],
        ),
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductGroupedProduct',
    uriTemplate: '/products/{productId}/grouped-products',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'grouped_products',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'List member products of a grouped-type product',
                description: 'Grouped-type only. Returns the child products bundled inside a grouped parent (each row carries qty + sortOrder + the associated child Product).',
            ),
        ),
    ],
    graphQlOperations: []
)]
class ProductGroupedProduct extends BaseProductGroupedProduct
{
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function associated_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'associated_product_id');
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(?int $value): void
    {
        $this->qty = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(?int $value): void
    {
        $this->sort_order = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getAssociatedProductId(): ?int
    {
        return $this->associated_product_id;
    }

    public function setAssociatedProductId(?int $value): void
    {
        $this->associated_product_id = $value;
    }

    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(?int $value): void
    {
        $this->product_id = $value;
    }
}
