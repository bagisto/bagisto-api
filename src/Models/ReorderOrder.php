<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GraphQl\Mutation;
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
