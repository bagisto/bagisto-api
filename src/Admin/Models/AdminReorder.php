<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminReorderInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminReorderProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReorder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/orders/{id}/reorder',
            input: false,
            processor: AdminReorderProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin: Customer Order creation'],
                summary: 'Reorder an order',
                description: "Builds a fresh admin draft cart from the given order's items and returns the new cart ID. The admin can then finalise the order in `admin.sales.orders.create`. Returns `success: false` if the order can't be reordered (guest order or any item is no longer saleable).",
                responses: [
                    '201' => new Model\Response(
                        description: 'Reorder accepted; a new draft cart was created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Reorder successful. A new draft cart has been created.',
                                    'cartId' => 314,
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
            input: AdminReorderInput::class,
            output: self::class,
            processor: AdminReorderProcessor::class,
            description: "Build a fresh admin draft cart from a previous order's items.",
        ),
    ]
)]
class AdminReorder
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;

    #[ApiProperty(writable: false)]
    public ?int $cart_id = null;
}
