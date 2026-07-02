<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerReviewProvider;

/**
 * Customer Review API Resource
 *
 * Returns all reviews submitted by the authenticated customer.
 * This is a read-only, customer-scoped resource.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerReview',
    uriTemplate: '/customer-reviews',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-reviews',
            provider: CustomerReviewProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Review'],
                summary: 'List reviews submitted by the authenticated customer',
                description: 'Returns every review written by the authenticated customer (all statuses). Each row embeds the reviewed `product` and `customer` as IRIs. Requires a customer Bearer token.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of the customer\'s reviews.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'        => 133,
                                        'name'      => '',
                                        'title'     => 'Great product',
                                        'rating'    => 5,
                                        'comment'   => 'Really enjoyed using this. Highly recommended.',
                                        'status'    => 'pending',
                                        'createdAt' => '2026-07-02T11:35:27+05:30',
                                        'updatedAt' => '2026-07-02T11:35:27+05:30',
                                        'product'   => '/api/shop/products/1',
                                        'customer'  => '/api/shop/customers/1532',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customer-reviews/{id}',
            provider: CustomerReviewProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Review'],
                summary: 'Get a single review by ID (customer-scoped)',
                description: 'Returns one review owned by the authenticated customer. Returns 404 if the review does not belong to the caller. Requires a customer Bearer token.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The requested review.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 133,
                                    'name'      => '',
                                    'title'     => 'Great product',
                                    'rating'    => 5,
                                    'comment'   => 'Really enjoyed using this. Highly recommended.',
                                    'status'    => 'pending',
                                    'createdAt' => '2026-07-02T11:35:27+05:30',
                                    'updatedAt' => '2026-07-02T11:35:27+05:30',
                                    'product'   => '/api/shop/products/1',
                                    'customer'  => '/api/shop/customers/1532',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'Review not found or not owned by the caller.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
        ),
        new QueryCollection(
            provider: CustomerReviewProvider::class,
            paginationType: 'cursor',
            args: [
                'status'  => ['type' => 'String', 'description' => 'Filter reviews by status (pending, approved, rejected)'],
                'rating'  => ['type' => 'Int', 'description' => 'Filter reviews by rating (1-5 stars)'],
                'first'   => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'    => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'   => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'  => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerReview extends \Webkul\Product\Models\ProductReview
{
    /** @var string */
    protected $table = 'product_reviews';

    /** @var list<string> */
    protected $fillable = [
        'comment',
        'title',
        'rating',
        'status',
        'product_id',
        'customer_id',
        'name',
    ];

    /** @var array<string, string> */
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
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Product relationship for API
     */
    #[ApiProperty(writable: false, description: 'Reviewed product')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Customer relationship for API
     */
    #[ApiProperty(writable: false, description: 'Customer who wrote the review')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
