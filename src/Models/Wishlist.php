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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Dto\CreateWishlistInput;
use Webkul\BagistoApi\Dto\DeleteWishlistInput;
use Webkul\BagistoApi\Resolver\WishlistQueryResolver;
use Webkul\BagistoApi\State\WishlistItemProvider;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            provider: WishlistItemProvider::class,
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Get a wishlist item by ID',
                description: 'Returns one wishlist item owned by the authenticated customer, with the `product`, `customer` and `channel` embedded as IRIs.',
                responses: [
                    '200' => new Response(
                        description: 'The wishlist item.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 199,
                                    'createdAt' => '2026-07-02T11:54:46+05:30',
                                    'updatedAt' => '2026-07-02T11:54:46+05:30',
                                    'product' => '/api/shop/products/1',
                                    'customer' => '/api/shop/customers/1533',
                                    'channel' => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Wishlist item not found or not owned by the caller.'),
                ],
            ),
        ),
        new GetCollection(
            provider: WishlistProvider::class,
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'List the customer\'s wishlist items',
                description: 'Returns every wishlist item for the authenticated customer in the current channel. Each row embeds `product`, `customer` and `channel` as IRIs.',
                responses: [
                    '200' => new Response(
                        description: 'List of wishlist items.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 199,
                                        'createdAt' => '2026-07-02T11:54:46+05:30',
                                        'updatedAt' => '2026-07-02T11:54:46+05:30',
                                        'product' => '/api/shop/products/1',
                                        'customer' => '/api/shop/customers/1533',
                                        'channel' => '/api/shop/channels/1',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new Parameter(
                        name: 'sort',
                        in: 'query',
                        description: 'Column to sort by: `id` (default) or `created_at`. Compound form also accepted, e.g. `created_at-desc`.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => ['id', 'created_at', 'id-asc', 'id-desc', 'created_at-asc', 'created_at-desc'],
                        ],
                    ),
                    new Parameter(
                        name: 'order',
                        in: 'query',
                        description: 'Sort direction: `asc` (default) or `desc`. Use `desc` to show the most recently added items first.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => ['asc', 'desc'],
                        ],
                    ),
                ],
            ),
        ),
        new Post(
            processor: WishlistProcessor::class,
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Create a wishlist item',
                description: 'Add a product to the customer\'s wishlist.',
                requestBody: new RequestBody(
                    description: 'Wishlist item details',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['productId'],

                                'properties' => [
                                    'productId' => [
                                        'type' => 'integer',
                                        'format' => 'int64',
                                        'example' => 1,
                                    ],
                                ],
                            ],

                            'example' => [
                                'productId' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Product added to wishlist.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 199,
                                    'createdAt' => '2026-07-02T11:54:46+05:30',
                                    'updatedAt' => '2026-07-02T11:54:46+05:30',
                                    'product' => '/api/shop/products/1',
                                    'customer' => '/api/shop/customers/1533',
                                    'channel' => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing/invalid productId.'),
                ],
            ),
        ),
        new Delete(
            processor: WishlistProcessor::class,
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Remove a wishlist item',
                description: 'Removes the wishlist item by ID. Returns 204 No Content on success.',
                responses: [
                    '204' => new Response(description: 'Wishlist item removed. No content.'),
                    '404' => new Response(description: 'Wishlist item not found or not owned by the caller.'),
                ],
            ),
        ),
        new Post(
            name: 'toggle_post',
            uriTemplate: '/wishlists/toggle',
            processor: WishlistProcessor::class,
            openapi: new Operation(
                tags: ['Wishlist'],
                summary: 'Toggle a product in wishlist',
                description: 'Add product to wishlist if not present, or remove if already present.',
                requestBody: new RequestBody(
                    description: 'Toggle wishlist item',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['productId'],

                                'properties' => [
                                    'productId' => [
                                        'type' => 'integer',
                                        'format' => 'int64',
                                        'example' => 1,
                                    ],
                                ],
                            ],

                            'example' => [
                                'productId' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Product toggled. `message` states whether it was added or removed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 200,
                                    'createdAt' => '2026-07-02T11:55:12+05:30',
                                    'updatedAt' => '2026-07-02T11:55:12+05:30',
                                    'message' => 'Item Successfully Added To Wishlist',
                                    'product' => '/api/shop/products/1',
                                    'customer' => '/api/shop/customers/1533',
                                    'channel' => '/api/shop/channels/1',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing/invalid productId.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: WishlistQueryResolver::class,
            description: 'One wishlist item of the authenticated customer on the current channel; any other item is not found. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
        new QueryCollection(
            provider: WishlistProvider::class,
            description: 'The authenticated customer\'s wishlist on the current channel. `sort` is `id` or `created_at` (or `column-asc`/`column-desc`) and `order` is `asc` (default) or `desc`. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 30 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            paginationType: 'cursor',
            extraArgs: [
                'sort' => ['type' => 'String'],
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
                    'type' => 'Int',
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
            processor: WishlistProcessor::class,
        ),
    ],
)]
class Wishlist extends \Webkul\Customer\Models\Wishlist
{
    /**
     * Core 2.5.0 added boolean and integer casts here. The API contract keeps the plain
     * database values (0/1) that Bagisto 2.4 returned, so the subclass does not inherit them.
     */
    protected $casts = [
        'additional' => 'array',
    ];

    /**
     * The appends.
     */
    protected $appends = ['message'];

    /**
     * The response message.
     */
    public ?string $responseMessage = null;

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get message attribute.
     */
    public function getMessageAttribute(): ?string
    {
        return $this->responseMessage;
    }

    /**
     * Set message.
     */
    public function setMessage(string $message): self
    {
        $this->responseMessage = $message;

        return $this;
    }

    /**
     * Product relationship for API.
     */
    #[ApiProperty(writable: false, description: 'Associated product')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Customer relationship for API.
     */
    #[ApiProperty(writable: false, description: 'Customer who added the item')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Channel relationship for API.
     */
    #[ApiProperty(writable: false, description: 'Channel where item was added')]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
