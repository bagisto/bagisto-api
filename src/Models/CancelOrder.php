<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CancelOrderInput;
use Webkul\BagistoApi\State\CancelOrderProcessor;

/**
 * Cancel Order Response Model
 *
 * Response object for the cancel order action.
 * Delegates to Bagisto's OrderRepository::cancel() which checks
 * canCancel(), dispatches events, restores inventory, and updates status.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CancelOrder',
    description: 'Cancel a customer order',
    operations: [
        new Post(
            uriTemplate: '/cancel-order',
            input: CancelOrderInput::class,
            processor: CancelOrderProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Order'],
                summary: 'Cancel a customer order',
                description: 'Cancels the given pending order owned by the authenticated customer. Returns success/failure status.',
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
                                        'description' => 'The ID of the order to cancel.',
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
            input: CancelOrderInput::class,
            output: self::class,
            processor: CancelOrderProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class CancelOrder
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
    public ?string $status = null;

    public function __construct(
        ?bool $success = null,
        ?string $message = null,
        ?int $orderId = null,
        ?string $status = null,
        ?int $id = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->orderId = $orderId;
        $this->status = $status;
        $this->id = $id ?? $orderId ?? 1;
    }
}
