<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCreateDraftCartInput;
use Webkul\BagistoApi\Admin\State\AdminDraftCartProcessor;

/**
 * Fresh Create-Order entry — bootstraps an empty admin draft cart
 * (`is_active = false`) for a customer. Distinct from the Reorder action
 * (`POST /api/admin/orders/{id}/reorder`) which builds the cart from an
 * existing order's items.
 *
 * REST    : POST /api/admin/customers/{customerId}/draft-carts
 *           (customer-nested URL — customer comes from the path, body is empty)
 * GraphQL : createAdminDraftCart(input: { customerId: Int! })
 *
 * Returns `{ cartId, customerId }` so the client can immediately switch to the
 * cart-keyed write endpoints (`POST /api/admin/carts/{id}/items`, etc.) for
 * everything else.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminDraftCart',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/{customerId}/draft-carts',
            uriVariables: [
                'customerId' => new Link(parameterName: 'customerId', fromClass: AdminDraftCart::class, identifiers: ['cartId']),
            ],
            input: false,
            processor: AdminDraftCartProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Create an empty draft cart for a customer',
                description: 'Bootstraps an empty admin draft cart (`is_active = false`) for the given customer. The returned `cartId` is the handle the admin uses for the rest of the Create-Order flow (`POST /api/admin/carts/{id}/items`, addresses, shipping, payment, place-order).',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(required: false),
                responses: [
                    '201' => new Model\Response(
                        description: 'A new empty draft cart was created for the customer.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'cartId'     => 412,
                                    'customerId' => 7,
                                    'success'    => true,
                                    'message'    => 'Draft cart created.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCreateDraftCartInput::class,
            output: self::class,
            processor: AdminDraftCartProcessor::class,
            description: 'Create an empty admin draft cart for the given customer.',
        ),
    ]
)]
class AdminDraftCart
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $cartId = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
