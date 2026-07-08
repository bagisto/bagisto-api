<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\CreateProductReviewInput;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ProductReviewUpdateProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductReview',
    uriTemplate: '/reviews',
    operations: [
        new GetCollection(
            uriTemplate: '/reviews',
            provider: ProductReviewProvider::class,
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List product reviews',
                description: 'Returns product reviews. Defaults to `approved` reviews only; pass `status` to override. Supports filtering by `product_id`, `status`, and `rating`, with `page` + `per_page` (alias `limit`, max 50) pagination. Mirrors the GraphQL `productReviews` query.',
                parameters: [
                    new Parameter(name: 'product_id', in: 'query', description: 'Filter by product ID', required: false, schema: ['type' => 'integer', 'example' => 1]),
                    new Parameter(name: 'status', in: 'query', description: 'Filter by status. Defaults to `approved` when omitted.', required: false, schema: ['type' => 'string', 'enum' => ['approved', 'pending'], 'example' => 'approved']),
                    new Parameter(name: 'rating', in: 'query', description: 'Filter by star rating (1-5)', required: false, schema: ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 5]),
                    new Parameter(name: 'page', in: 'query', description: 'Page number (1-based)', required: false, schema: ['type' => 'integer', 'default' => 1, 'example' => 1]),
                    new Parameter(name: 'per_page', in: 'query', description: 'Items per page (alias: `limit`). Default 30, max 50.', required: false, schema: ['type' => 'integer', 'default' => 30, 'maximum' => 50, 'example' => 10]),
                ],
                responses: [
                    '200' => new Response(
                        description: 'List of product reviews.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 2,
                                        'name' => 'lxbfYeaa',
                                        'title' => 'Mr.',
                                        'rating' => 1,
                                        'comment' => '1',
                                        'status' => 'approved',
                                        'createdAt' => '2025-05-27T23:20:27+05:30',
                                        'updatedAt' => '2025-09-03T18:10:50+05:30',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/reviews/{id}',
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Get a single product review by ID',
                responses: [
                    '200' => new Response(
                        description: 'The product review.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 2,
                                    'name' => 'lxbfYeaa',
                                    'title' => 'Mr.',
                                    'rating' => 1,
                                    'comment' => '1',
                                    'status' => 'approved',
                                    'createdAt' => '2025-05-27T23:20:27+05:30',
                                    'updatedAt' => '2025-09-03T18:10:50+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Review not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/reviews',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups' => ['mutation'],
                'allow_extra_attributes' => true,
            ],
            openapi: new Operation(
                tags: ['Customer Review'],
                summary: 'Create a product review',
                description: 'Creates a review for a product on behalf of the authenticated customer. Review starts in `pending` status until approved by admin.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['product_id', 'title', 'comment', 'rating', 'name'],
                                'properties' => [
                                    'product_id' => ['type' => 'integer', 'example' => 2, 'description' => 'ID of the product being reviewed'],
                                    'title' => ['type' => 'string', 'example' => 'Great product', 'description' => 'Short review title'],
                                    'comment' => ['type' => 'string', 'example' => 'Works exactly as described, highly recommended.', 'description' => 'Full review body'],
                                    'rating' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 5, 'description' => 'Star rating (1-5)'],
                                    'name' => ['type' => 'string', 'example' => 'John Doe', 'description' => 'Reviewer display name'],
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com', 'description' => 'Optional: reviewer email (for guests)'],
                                    'attachments' => ['type' => 'string', 'description' => 'Optional: JSON-encoded attachment metadata'],
                                ],
                            ],
                            'example' => [
                                'product_id' => 1,
                                'title' => 'Great product',
                                'comment' => 'Really enjoyed using this. Highly recommended.',
                                'rating' => 5,
                                'name' => 'John Doe',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Review created. Starts in `pending` status until an admin approves it.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 133,
                                    'name' => '',
                                    'title' => 'Great product',
                                    'rating' => 5,
                                    'comment' => 'Really enjoyed using this. Highly recommended.',
                                    'status' => 'pending',
                                    'createdAt' => '2026-07-02T11:35:27+05:30',
                                    'updatedAt' => '2026-07-02T11:35:27+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Validation failed (missing product_id/rating, invalid rating, etc.).'),
                ],
            ),
        ),
        new Patch(
            uriTemplate: '/reviews/{id}',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups' => ['mutation'],
                'allow_extra_attributes' => true,
            ],
            openapi: new Operation(
                tags: ['Customer Review'],
                summary: 'Update an existing product review',
                description: 'Updates a customer-owned product review. Only the author (matched via Bearer token) can modify their review.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/merge-patch+json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'title' => ['type' => 'string', 'example' => 'Updated title'],
                                    'comment' => ['type' => 'string', 'example' => 'Edited my review after more use.'],
                                    'rating' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 4],
                                    'name' => ['type' => 'string', 'example' => 'John Doe'],
                                ],
                            ],
                            'example' => [
                                'title' => 'Updated title',
                                'comment' => 'Edited my review after more use.',
                                'rating' => 4,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(
                        description: 'Review updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 133,
                                    'name' => '',
                                    'title' => 'Updated title',
                                    'rating' => 4,
                                    'comment' => 'Edited my review after more use.',
                                    'status' => 'pending',
                                    'createdAt' => '2026-07-02T11:35:27+05:30',
                                    'updatedAt' => '2026-07-02T11:37:52+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Review not found or not owned by the caller.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/reviews/{id}',
            openapi: new Operation(
                tags: ['Customer Review'],
                summary: 'Delete a product review',
                description: 'Deletes a customer-owned product review. Returns 204 No Content on success.',
                responses: [
                    '204' => new Response(description: 'Review deleted. No content.'),
                    '404' => new Response(description: 'Review not found or not owned by the caller.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: ProductReviewProvider::class,
            args: [
                'product_id' => ['type' => 'Int', 'description' => 'Filter reviews by product ID'],
                'status' => ['type' => 'String', 'description' => 'Filter reviews by status (pending, approved, rejected)'],
                'rating' => ['type' => 'Int', 'description' => 'Filter reviews by rating (1-5 stars)'],
                'first' => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last' => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after' => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before' => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ]
        ),
        new Query(resolver: BaseQueryItemResolver::class),
        new Mutation(
            name: 'create',
            input: CreateProductReviewInput::class,
            output: ProductReview::class,
            processor: ProductReviewProcessor::class,
        ),
        new Mutation(
            name: 'update',
            input: UpdateProductReviewInput::class,
            output: ProductReview::class,
            provider: ProductReviewUpdateProvider::class,
            processor: ProductReviewProcessor::class,
            description: 'Update an existing product review'
        ),
        new DeleteMutation(
            name: 'delete',
            description: 'Delete a product review'
        ),
    ]
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductReview',
    uriTemplate: '/products/{productId}/reviews',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'reviews',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            provider: ProductReviewProvider::class,
            openapi: new Operation(
                tags: ['Product'],
                summary: 'List reviews for a product',
                description: 'Returns reviews scoped to the given product ID. Defaults to `approved` reviews only; pass `status` to override. Supports filtering by `status` and `rating`, with `page` + `per_page` (alias `limit`, max 50) pagination.',
                parameters: [
                    new Parameter(name: 'status', in: 'query', description: 'Filter by status. Defaults to `approved` when omitted.', required: false, schema: ['type' => 'string', 'enum' => ['approved', 'pending'], 'example' => 'approved']),
                    new Parameter(name: 'rating', in: 'query', description: 'Filter by star rating (1-5)', required: false, schema: ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 5]),
                    new Parameter(name: 'page', in: 'query', description: 'Page number (1-based)', required: false, schema: ['type' => 'integer', 'default' => 1, 'example' => 1]),
                    new Parameter(name: 'per_page', in: 'query', description: 'Items per page (alias: `limit`). Default 30, max 50.', required: false, schema: ['type' => 'integer', 'default' => 30, 'maximum' => 50, 'example' => 10]),
                ],
                responses: [
                    '200' => new Response(
                        description: 'List of reviews for the product.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 2,
                                        'name' => 'lxbfYeaa',
                                        'title' => 'Mr.',
                                        'rating' => 1,
                                        'comment' => '1',
                                        'status' => 'approved',
                                        'createdAt' => '2025-05-27T23:20:27+05:30',
                                        'updatedAt' => '2025-09-03T18:10:50+05:30',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
class ProductReview extends \Webkul\Product\Models\ProductReview
{
    protected $fillable = [
        'comment',
        'title',
        'rating',
        'status',
        'product_id',
        'customer_id',
        'name',
    ];

    protected $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'customer_id' => 'int',
        'title' => 'string',
        'comment' => 'string',
        'name' => 'string',
        'rating' => 'int',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Override __get to expose Eloquent attributes to Serializer
     * Removing public property declarations ensures this method is called
     * instead of property reflection accessing empty declared values.
     */
    public function __get($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttribute($key);
        }

        return parent::__get($key);
    }

    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * Override __isset to ensure isset() works correctly with __get()
     * This is critical for Symfony PropertyAccessor which checks isset() before reading.
     */
    public function __isset($key)
    {
        if ($this->hasAttribute($key)) {
            return true;
        }

        return parent::__isset($key);
    }

    /**
     * Override __set to handle attribute setting properly
     */
    public function __set($key, $value)
    {
        if (in_array($key, ['id', 'product_id', 'customer_id', 'title', 'comment', 'rating', 'name', 'email', 'status', 'created_at', 'updated_at'])) {
            $this->setAttribute($key, $value);
        } else {
            parent::__set($key, $value);
        }
    }

    public function getAttachmentsAttribute()
    {
        return $this->getAttachmentUrls();
    }

    public function getAttachmentUrls()
    {
        return $this->images->first() ? $this->images->map(function ($item) {
            return [
                'type' => $item->type,
                'url' => $item->url(),
            ];
        })->toJson() : null;
    }
}
