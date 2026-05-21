<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCartShippingMethodsProvider;

/**
 * One shipping rate row exposed by `GET /api/admin/carts/{cartId}/shipping-methods`.
 *
 * The provider calls `Shipping::collectRates()` against the cart, flattens the
 * grouped result into individual rate rows and wraps them in the standard
 * admin `{ data, meta }` collection envelope.
 *
 * Sequence rule: both billing AND shipping addresses must be saved on the
 * cart first — otherwise the provider throws HTTP 409.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCartShippingRate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/carts/{cartId}/shipping-methods',
            provider: AdminCartShippingMethodsProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Carts'],
                summary: 'List available shipping rates for the draft cart',
                description: 'Calls `Shipping::collectRates()` against the cart and returns the available rates. Requires both billing AND shipping addresses to already be saved — returns HTTP 409 otherwise.',
                parameters: [
                    new Model\Parameter('cartId', 'path', 'Cart ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Available shipping rates.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        ['carrierCode' => 'flatrate', 'carrierTitle' => 'Flat Rate', 'method' => 'flatrate_flatrate', 'methodTitle' => 'Fixed', 'price' => 10, 'formattedPrice' => '$10.00', 'baseTotal' => 10, 'formattedBaseTotal' => '$10.00'],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
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
            provider: AdminCartShippingMethodsProvider::class,
            paginationType: 'cursor',
            description: 'Available shipping rates for a draft cart.',
            args: [
                'cartId' => ['type' => 'Int!', 'description' => 'Cart ID'],
            ],
        ),
    ]
)]
class AdminCartShippingRate
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $method = null;

    #[ApiProperty(writable: false)]
    public ?string $carrierCode = null;

    #[ApiProperty(writable: false)]
    public ?string $carrierTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $methodTitle = null;

    #[ApiProperty(writable: false)]
    public ?float $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    #[ApiProperty(writable: false)]
    public ?float $baseTotal = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedBaseTotal = null;
}
