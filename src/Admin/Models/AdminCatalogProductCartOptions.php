<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminCatalogProductCartOptionsResolver;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCartOptionsProvider;

/**
 * Option set the admin Create Order drawer needs to add a product to a draft cart,
 * shaped by the product type exactly as the admin catalog option endpoints return it.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductCartOptions',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/catalog/products/{productId}/cart-options',
            requirements: ['productId' => '\d+'],
            provider: AdminCatalogProductCartOptionsProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Cart options for a product (Create Order)',
                description: 'Returns what the admin Create Order drawer shows before adding a product to a draft cart. `options` depends on `type`: configurable returns the attributes, variant index and variant prices; bundle returns the bundle options; grouped returns the saleable associated products; downloadable returns the links; simple and virtual return the customizable options with `initialPrice`. Other types return `options: null` (booking products use `GET /booking-products/{productId}/config`).',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 6]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Cart options for the product.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'examples' => [
                                    'grouped' => [
                                        'value' => [[
                                            'productId' => 6,
                                            'type' => 'grouped',
                                            'initialPrice' => null,
                                            'options' => [[
                                                'id' => 7,
                                                'name' => 'Arctic Touchscreen Gloves',
                                                'qty' => 1,
                                                'price' => 21,
                                                'formatted_price' => '$21.00',
                                            ]],
                                        ]],
                                    ],
                                    'downloadable' => [
                                        'value' => [[
                                            'productId' => 9,
                                            'type' => 'downloadable',
                                            'initialPrice' => null,
                                            'options' => [[
                                                'id' => 1,
                                                'title' => 'E-book PDF',
                                                'price' => '5.0000',
                                                'formatted_price' => '$5.00',
                                            ]],
                                        ]],
                                    ],
                                    'simple' => [
                                        'value' => [[
                                            'productId' => 1,
                                            'type' => 'simple',
                                            'initialPrice' => 14,
                                            'options' => [],
                                        ]],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Product not found.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing the catalog.products permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminCatalogProductCartOptionsResolver::class,
            args: ['productId' => ['type' => 'Int!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Cart options for the admin Create Order product drawer.',
        ),
    ],
)]
class AdminCatalogProductCartOptions
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $product_id = null;

    #[ApiProperty(writable: false, description: 'Product type the options are shaped for.')]
    #[Groups(['query'])]
    public ?string $type = null;

    #[ApiProperty(writable: false, description: 'Simple and virtual products only: the minimal price before customizable options.')]
    #[Groups(['query'])]
    public ?float $initial_price = null;

    /**
     * @var array<int|string, mixed>|null
     */
    #[ApiProperty(writable: false, description: 'Type-specific option set (plain JSON).')]
    #[Groups(['query'])]
    public ?array $options = null;
}
