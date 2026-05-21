<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCartPaymentMethodsProvider;

/**
 * One supported payment method row exposed by
 * `GET /api/admin/carts/{cartId}/payment-methods`.
 *
 * Sequence rule: a shipping method must already be selected on the cart
 * (returns HTTP 409 otherwise).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCartPaymentMethod',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/carts/{cartId}/payment-methods',
            provider: AdminCartPaymentMethodsProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Carts'],
                summary: 'List supported payment methods for the draft cart',
                description: 'Returns `Payment::getSupportedPaymentMethods()`. A shipping method must already be selected on the cart (HTTP 409 otherwise).',
                parameters: [
                    new Model\Parameter('cartId', 'path', 'Cart ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Supported payment methods.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        ['method' => 'cashondelivery', 'methodTitle' => 'Cash On Delivery', 'description' => '', 'sort' => 1, 'image' => null],
                                        ['method' => 'moneytransfer', 'methodTitle' => 'Money Transfer', 'description' => '', 'sort' => 2, 'image' => null],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 2, 'lastPage' => 1, 'total' => 2, 'from' => 1, 'to' => 2],
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
            provider: AdminCartPaymentMethodsProvider::class,
            paginationType: 'cursor',
            description: 'Supported payment methods for a draft cart.',
            args: [
                'cartId' => ['type' => 'Int!', 'description' => 'Cart ID'],
            ],
        ),
    ]
)]
class AdminCartPaymentMethod
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $method = null;

    #[ApiProperty(writable: false)]
    public ?string $methodTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?int $sort = null;

    #[ApiProperty(writable: false)]
    public ?string $image = null;
}
