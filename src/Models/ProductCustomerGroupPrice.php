<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
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
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List tier (customer-group) prices for a product',
                description: 'Returns the per-customer-group quantity-based discount rows ("buy N for X") for the given product. Read-only — admin endpoints under /api/admin handle creation/edits. A null customerGroupId applies to all groups; valueType is "fixed" or "discount".',
                responses: [
                    '200' => new Response(
                        description: 'Tier prices for the product',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 7,
                                        'qty' => 10,
                                        'valueType' => 'fixed',
                                        'value' => '7.5000',
                                        'productId' => 2611,
                                        'customerGroupId' => null,
                                        'createdAt' => '2026-05-26T12:51:19+05:30',
                                        'updatedAt' => '2026-05-26T12:51:32+05:30',
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
