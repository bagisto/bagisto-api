<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Dto\CreateWishlistInput;
use Webkul\BagistoApi\Dto\DeleteWishlistInput;
use Webkul\BagistoApi\Resolver\WishlistQueryResolver;
use Webkul\BagistoApi\State\WishlistItemProvider;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;

/**
 * Wishlist Item API Resource
 *
 * Allows customers to add and manage products in their wishlist
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            provider: WishlistItemProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Wishlist'],
                summary: 'Get a wishlist item by ID',
                description: 'Returns one wishlist item owned by the authenticated customer, with the `product`, `customer` and `channel` embedded as IRIs.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The wishlist item.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 199,
                                    'createdAt' => '2026-07-02T11:54:46+05:30',
                                    'updatedAt' => '2026-07-02T11:54:46+05:30',
                                    'product'   => '/api/shop/products/1',
                                    'customer'  => '/api/shop/customers/1533',
                                    'channel'   => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'Wishlist item not found or not owned by the caller.'),
                ],
            ),
        ),
        new GetCollection(
            provider: WishlistProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Wishlist'],
                summary: 'List the customer\'s wishlist items',
                description: 'Returns every wishlist item for the authenticated customer in the current channel. Each row embeds `product`, `customer` and `channel` as IRIs.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of wishlist items.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'        => 199,
                                        'createdAt' => '2026-07-02T11:54:46+05:30',
                                        'updatedAt' => '2026-07-02T11:54:46+05:30',
                                        'product'   => '/api/shop/products/1',
                                        'customer'  => '/api/shop/customers/1533',
                                        'channel'   => '/api/shop/channels/1',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'sort',
                        in: 'query',
                        description: 'Column to sort by: `id` (default) or `created_at`. Compound form also accepted, e.g. `created_at-desc`.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['id', 'created_at', 'id-asc', 'id-desc', 'created_at-asc', 'created_at-desc']],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'order',
                        in: 'query',
                        description: 'Sort direction: `asc` (default) or `desc`. Use `desc` to show the most recently added items first.',
                        required: false,
                        schema: ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ),
                ],
            ),
        ),
        new Post(
            processor: WishlistProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Wishlist'],
                summary: 'Create a wishlist item',
                description: 'Add a product to the customer\'s wishlist.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'Wishlist item details',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['productId'],
                                'properties' => [
                                    'productId'    => ['type' => 'integer', 'format' => 'int64', 'example' => 1],
                                ],
                            ],
                            'example' => [
                                'productId' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Product added to wishlist.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 199,
                                    'createdAt' => '2026-07-02T11:54:46+05:30',
                                    'updatedAt' => '2026-07-02T11:54:46+05:30',
                                    'product'   => '/api/shop/products/1',
                                    'customer'  => '/api/shop/customers/1533',
                                    'channel'   => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new \ApiPlatform\OpenApi\Model\Response(description: 'Missing/invalid productId.'),
                ],
            ),
        ),
        new Delete(
            processor: WishlistProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Wishlist'],
                summary: 'Remove a wishlist item',
                description: 'Removes the wishlist item by ID. Returns 204 No Content on success.',
                responses: [
                    '204' => new \ApiPlatform\OpenApi\Model\Response(description: 'Wishlist item removed. No content.'),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'Wishlist item not found or not owned by the caller.'),
                ],
            ),
        ),
        new Post(
            name: 'toggle_post',
            uriTemplate: '/wishlists/toggle',
            processor: WishlistProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Wishlist'],
                summary: 'Toggle a product in wishlist',
                description: 'Add product to wishlist if not present, or remove if already present.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'Toggle wishlist item',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['productId'],
                                'properties' => [
                                    'productId' => ['type' => 'integer', 'format' => 'int64', 'example' => 1],
                                ],
                            ],
                            'example' => [
                                'productId' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Product toggled. `message` states whether it was added or removed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 200,
                                    'createdAt' => '2026-07-02T11:55:12+05:30',
                                    'updatedAt' => '2026-07-02T11:55:12+05:30',
                                    'message'   => 'Item Successfully Added To Wishlist',
                                    'product'   => '/api/shop/products/1',
                                    'customer'  => '/api/shop/customers/1533',
                                    'channel'   => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new \ApiPlatform\OpenApi\Model\Response(description: 'Missing/invalid productId.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: WishlistQueryResolver::class),
        new QueryCollection(
            provider: WishlistProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'sort'  => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: CreateWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
        new Mutation(
            name: 'toggle',
            args: [
                'productId' => [
                    'type'        => 'Int',
                    'description' => 'ID of the product to toggle in the wishlist.',
                ],
            ],
            input: CreateWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
        new Mutation(
            name: 'delete',
            input: DeleteWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
    ],
)]
class Wishlist extends \Webkul\Customer\Models\Wishlist
{
    protected $appends = ['message'];

    public ?string $responseMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    public function getMessageAttribute(): ?string
    {
        return $this->responseMessage;
    }

    public function setMessage(string $message): self
    {
        $this->responseMessage = $message;

        return $this;
    }

    /**
     * Product relationship for API
     */
    #[ApiProperty(writable: false, description: 'Associated product')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Customer relationship for API
     */
    #[ApiProperty(writable: false, description: 'Customer who added the item')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Channel relationship for API
     */
    #[ApiProperty(writable: false, description: 'Channel where item was added')]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
