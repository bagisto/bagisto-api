<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\DeleteAllWishlistsInput;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;

/**
 * Delete All Wishlists Response Model
 *
 * Response object for bulk delete wishlist operations
 */
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
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $deletedCount = null;

    public function __construct(?string $message = null, ?int $deletedCount = null, ?int $id = null)
    {
        $this->message = $message;
        $this->deletedCount = $deletedCount;
        $this->id = $id ?? 1;
    }
}
