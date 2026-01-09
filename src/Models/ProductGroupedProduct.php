<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\State\GroupedProductsProvider;
use Webkul\Product\Models\ProductGroupedProduct as BaseProductGroupedProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
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
        new Query,
    ]
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
