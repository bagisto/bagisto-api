<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\ReorderInput;
use Webkul\BagistoApi\State\ReorderProcessor;

/**
 * Reorder Response Model
 *
 * Response object for the reorder action.
 * Re-adds items from a previous order to the customer's cart
 * using Cart::addProduct(), same as the Shop controller.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReorderOrder',
    description: 'Reorder items from a previous customer order',
    operations: [
        new Post(
            uriTemplate: '/reorder',
            input: ReorderInput::class,
            processor: ReorderProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Order'],
                summary: 'Reorder items from a previous customer order',
                description: 'Re-adds the items from the given completed/canceled order to the authenticated customer\'s cart. Same flow as the storefront "Reorder" button.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['orderId'],
                                'properties' => [
                                    'orderId' => [
                                        'type'        => 'integer',
                                        'description' => 'The ID of the previous order whose items should be added back to the cart.',
                                        'example'     => 411,
                                    ],
                                ],
                            ],
                            'example' => ['orderId' => 411],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ReorderInput::class,
            output: self::class,
            processor: ReorderProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class ReorderOrder
{
    #[ApiProperty(identifier: false, writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $orderId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsAddedCount = null;

    public function __construct(
        ?bool $success = null,
        ?string $message = null,
        ?int $orderId = null,
        ?int $itemsAddedCount = null,
        ?int $id = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->orderId = $orderId;
        $this->itemsAddedCount = $itemsAddedCount;
        $this->id = $id ?? $orderId ?? 1;
    }
}
