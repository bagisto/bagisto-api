<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\DeleteMutation;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
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
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate: '/reviews',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List product reviews',
                description: 'Returns all product reviews. Mirrors the GraphQL `productReviews` query.',
            ),
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/reviews/{id}',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'Get a single product review by ID',
            ),
        ),
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/reviews',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups'                 => ['mutation'],
                'allow_extra_attributes' => true,
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Review'],
                summary: 'Create a product review',
                description: 'Creates a review for a product on behalf of the authenticated customer. Review starts in `pending` status until approved by admin.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['product_id', 'title', 'comment', 'rating', 'name'],
                                'properties' => [
                                    'product_id'  => ['type' => 'integer', 'example' => 2, 'description' => 'ID of the product being reviewed'],
                                    'title'       => ['type' => 'string', 'example' => 'Great product', 'description' => 'Short review title'],
                                    'comment'     => ['type' => 'string', 'example' => 'Works exactly as described, highly recommended.', 'description' => 'Full review body'],
                                    'rating'      => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 5, 'description' => 'Star rating (1-5)'],
                                    'name'        => ['type' => 'string', 'example' => 'John Doe', 'description' => 'Reviewer display name'],
                                    'email'       => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com', 'description' => 'Optional: reviewer email (for guests)'],
                                    'attachments' => ['type' => 'string', 'description' => 'Optional: JSON-encoded attachment metadata'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new \ApiPlatform\Metadata\Patch(
            uriTemplate: '/reviews/{id}',
            processor: ProductReviewProcessor::class,
            denormalizationContext: [
                'groups'                 => ['mutation'],
                'allow_extra_attributes' => true,
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Review'],
                summary: 'Update an existing product review',
                description: 'Updates a customer-owned product review. Only the author (matched via Bearer token) can modify their review.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/merge-patch+json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'title'   => ['type' => 'string', 'example' => 'Updated title'],
                                    'comment' => ['type' => 'string', 'example' => 'Updated review body.'],
                                    'rating'  => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'example' => 4],
                                    'name'    => ['type' => 'string', 'example' => 'John Doe'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new \ApiPlatform\Metadata\Delete(
            uriTemplate: '/reviews/{id}',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Review'],
                summary: 'Delete a product review',
                description: 'Deletes a customer-owned product review.',
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: ProductReviewProvider::class,
            args: [
                'product_id' => ['type' => 'Int', 'description' => 'Filter reviews by product ID'],
                'status'     => ['type' => 'String', 'description' => 'Filter reviews by status (pending, approved, rejected)'],
                'rating'     => ['type' => 'Int', 'description' => 'Filter reviews by rating (1-5 stars)'],
                'first'      => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'       => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'      => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'     => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
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
        new \ApiPlatform\Metadata\GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List reviews for a product',
                description: 'Returns reviews scoped to the given product ID.',
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
        'id'          => 'int',
        'product_id'  => 'int',
        'customer_id' => 'int',
        'title'       => 'string',
        'comment'     => 'string',
        'name'        => 'string',
        'rating'      => 'int',
        'status'      => 'string',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
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
                'url'  => $item->url(),
            ];
        })->toJson() : null;
    }
}
