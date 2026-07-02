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
use Webkul\BagistoApi\State\BundleOptionProductsProvider;
use Webkul\Product\Models\ProductBundleOptionProduct as BaseProductBundleOptionProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/product-bundle-option-products/{id}',
    operations: [
        new GetCollection(
            uriTemplate: '/product-bundle-option-products',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'List bundle option products',
                description: 'Returns the selectable products inside bundle option groups. Used together with ProductBundleOption when displaying a bundle product detail page.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of bundle option products',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'            => 1,
                                        'qty'           => 1,
                                        'isUserDefined' => 1,
                                        'isDefault'     => 1,
                                        'sortOrder'     => 0,
                                        'bundleOption'  => '/api/shop/product_bundle_options/1',
                                        'product'       => '/api/shop/products/2512',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/product-bundle-option-products/{id}',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'Get a single bundle option product',
                description: 'Public endpoint. Returns one selectable product inside a bundle option group.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Bundle option product resource',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'            => 1,
                                    'qty'           => 1,
                                    'isUserDefined' => 1,
                                    'isDefault'     => 1,
                                    'sortOrder'     => 0,
                                    'bundleOption'  => '/api/shop/product_bundle_options/1',
                                    'product'       => '/api/shop/products/2512',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Bundle option product not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: BundleOptionProductsProvider::class,
            links: [
                new Link(
                    fromProperty: 'bundleOptionProducts',
                    fromClass: ProductBundleOption::class,
                    toClass: self::class,
                    identifiers: ['product_bundle_option_id'],
                ),
            ],
        ),
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class ProductBundleOptionProduct extends BaseProductBundleOptionProduct
{
    /**
     * Get the product bundle option product identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the parent bundle option.
     */
    public function bundle_option(): BelongsTo
    {
        return $this->belongsTo(ProductBundleOption::class, 'product_bundle_option_id');
    }

    /**
     * Get the related product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the quantity.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getQty(): ?int
    {
        return $this->qty;
    }

    /**
     * Set the quantity.
     */
    public function setQty(?int $value): void
    {
        $this->qty = $value;
    }

    /**
     * Check if quantity is user defined.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getIsUserDefined(): ?bool
    {
        return (bool) $this->is_user_defined;
    }

    /**
     * Set if quantity is user defined.
     */
    public function setIsUserDefined(?bool $value): void
    {
        $this->is_user_defined = $value;
    }

    /**
     * Get the sort order.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    /**
     * Set the sort order.
     */
    public function setSortOrder(?int $value): void
    {
        $this->sort_order = $value;
    }

    /**
     * Check if this is the default option.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getIsDefault(): ?bool
    {
        return (bool) $this->is_default;
    }

    /**
     * Set if this is the default option.
     */
    public function setIsDefault(?bool $value): void
    {
        $this->is_default = $value;
    }

    /**
     * Get the product ID.
     */
    #[ApiProperty(writable: true, readable: true)]
    #[Groups(['mutation'])]
    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    /**
     * Set the product ID.
     */
    public function setProductId(?int $value): void
    {
        $this->product_id = $value;
    }
}
