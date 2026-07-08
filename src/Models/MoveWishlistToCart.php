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
use Webkul\BagistoApi\Dto\CartData;
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
            output: CartData::class,
            processor: MoveWishlistToCartProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Move a wishlist item to the cart',
                description: 'Moves the given wishlist item into the authenticated customer\'s active cart and removes it from the wishlist. Returns the updated cart.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['wishlistItemId'],
                                'properties' => [
                                    'wishlistItemId' => ['type' => 'integer', 'example' => 202, 'description' => 'ID of the wishlist item to move'],
                                    'quantity' => ['type' => 'integer', 'example' => 1, 'description' => 'Quantity to add to cart (defaults to 1)'],
                                ],
                            ],
                            'example' => [
                                'wishlistItemId' => 202,
                                'quantity' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Item moved to cart. Returns the updated cart.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6885,
                                    'cartToken' => '6885',
                                    'customerId' => 1533,
                                    'channelId' => 1,
                                    'itemsCount' => 1,
                                    'items' => [
                                        [
                                            'id' => 7763,
                                            'cartId' => 6885,
                                            'productId' => 1,
                                            'name' => 'Coastal Breeze Men\'s Blue Zipper Hoodie',
                                            'sku' => 'COASTALBREEZEMENSHOODIE',
                                            'quantity' => 1,
                                            'price' => 100,
                                            'total' => 100,
                                            'type' => 'simple',
                                            'formattedPrice' => '$100.00',
                                            'formattedTotal' => '$100.00',
                                        ],
                                    ],
                                    'subtotal' => 100,
                                    'grandTotal' => 100,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'formattedSubtotal' => '$100.00',
                                    'formattedGrandTotal' => '$100.00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Wishlist item not found or not owned by the caller.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'move',
            args: [
                'wishlistItemId' => [
                    'type' => 'Int!',
                    'description' => 'ID of the wishlist item to move to cart.',
                ],
                'quantity' => [
                    'type' => 'Int',
                    'description' => 'Quantity of the item to add to cart (defaults to 1).',
                ],
            ],
            input: MoveWishlistToCartInput::class,
            output: CartData::class,
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
