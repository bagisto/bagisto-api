<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProvider;
use Webkul\Product\Models\ProductCustomerGroupPrice as BaseProductCustomerGroupPrice;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductCustomerGroupPrice',
    uriTemplate: '/products/{productId}/customer-group-prices',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'customer_group_prices',
            identifiers: ['id']
        ),
    ],
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            provider: ProductCustomerGroupPriceProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List tier (customer-group) prices for a product',
                description: 'Returns the per-customer-group quantity-based discount rows ("buy N for X") for the given product. Read-only — admin endpoints under /api/admin handle creation/edits.',
            ),
        ),
    ],
    graphQlOperations: []
)]
class ProductCustomerGroupPrice extends BaseProductCustomerGroupPrice
{
    protected $visible = [
        'id',
        'qty',
        'value_type',
        'value',
        'product_id',
        'customer_group_id',
        'created_at',
        'updated_at',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
