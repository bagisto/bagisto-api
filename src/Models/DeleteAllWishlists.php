<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\DeleteAllWishlistsInput;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DeleteAllWishlists',
    description: 'Delete all wishlist items for the authenticated customer',
    operations: [
        new Post(
            uriTemplate: '/delete-all-wishlists',
            input: DeleteAllWishlistsInput::class,
            processor: DeleteAllWishlistsProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Delete all wishlist items',
                description: 'Removes every wishlist item for the authenticated customer. Send an empty JSON body `{}`. Returns the count removed.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => new \ArrayObject,
                            ],

                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'All wishlist items removed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'message' => 'All wishlist items have been removed successfully',
                                    'deletedCount' => 1,
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
            input: DeleteAllWishlistsInput::class,
            output: self::class,
            processor: DeleteAllWishlistsProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class DeleteAllWishlists
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    /**
     * The deleted count.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $deletedCount = null;

    /**
     * Create a new instance.
     */
    public function __construct(?string $message = null, ?int $deletedCount = null, ?int $id = null)
    {
        $this->message = $message;
        $this->deletedCount = $deletedCount;
        $this->id = $id ?? 1;
    }
}
