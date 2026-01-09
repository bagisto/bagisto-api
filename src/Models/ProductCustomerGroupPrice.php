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
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProcessor;
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProvider;
use Webkul\Product\Models\ProductCustomerGroupPrice as BaseProductCustomerGroupPrice;

#[ApiResource(
    routePrefix: '/api/admin',
    uriTemplate: '/products/{productId}/customer-group-prices',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'customer_group_prices',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(provider: ProductCustomerGroupPriceProvider::class),
        new Post(processor: ProductCustomerGroupPriceProcessor::class),
    ],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/admin',
    uriTemplate: '/products/{productId}/customer-group-prices/{id}',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'customer_group_prices',
            identifiers: ['id']
        ),
        'id' => new Link(fromClass: ProductCustomerGroupPrice::class),
    ],
    operations: [
        new Get(provider: ProductCustomerGroupPriceProvider::class),
        new Patch(
            provider: ProductCustomerGroupPriceProvider::class,
            processor: ProductCustomerGroupPriceProcessor::class
        ),
        new Delete,
    ],
    graphQlOperations: []
)]

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'ProductCustomerGroupPrice',
    uriTemplate: '/customer-group-prices',
    operations: [
        new GetCollection(provider: ProductCustomerGroupPriceProvider::class),
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
    shortName: 'ProductCustomerGroupPrice',
    uriTemplate: '/customer-group-prices/{id}',
    operations: [
        new Get(provider: ProductCustomerGroupPriceProvider::class),
    ],
    graphQlOperations: [
        new Query,
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new DeleteMutation(name: 'delete'),
    ]
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
