<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\MoveWishlistToCartInput;
use Webkul\BagistoApi\State\MoveWishlistToCartProcessor;

/**
 * Move Wishlist to Cart Response Model
 *
 * Response object for move wishlist to cart operations
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'WishlistToCart',
    description: 'Move wishlist items to cart',
    operations: [
        new Post(
            uriTemplate: '/move-wishlist-to-carts',
            input: MoveWishlistToCartInput::class,
            processor: MoveWishlistToCartProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'move',
            args: [
                'wishlistItemId' => [
                    'type'        => 'Int!',
                    'description' => 'ID of the wishlist item to move to cart.',
                ],
                'quantity' => [
                    'type'        => 'Int',
                    'description' => 'Quantity of the item to add to cart (defaults to 1).',
                ],
            ],
            input: MoveWishlistToCartInput::class,
            output: self::class,
            processor: MoveWishlistToCartProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class MoveWishlistToCart
{
    #[ApiProperty(identifier: true, writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    public function __construct(?string $message = null, ?int $id = null)
    {
        $this->message = $message;
        $this->id = $id;
    }
}

