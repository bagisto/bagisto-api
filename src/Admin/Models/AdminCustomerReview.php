<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewDetailDto;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewListDto;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerReview',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Put(
            uriTemplate: '/customers/reviews/{id}',
            input: AdminCustomerReviewUpdateInput::class,
            provider: AdminCustomerReviewWriteProvider::class,
            processor: AdminCustomerReviewProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Update review status',
                description: 'Only the status field is editable; reviews originate from the storefront.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Review ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['status'],
                                'properties' => [
                                    'status' => ['type' => 'string', 'enum' => ['pending', 'approved', 'disapproved'], 'example' => 'approved'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Review updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 21, 'title' => 'Great product', 'comment' => 'Exactly as described.',
                                    'rating'    => 5, 'status' => 'approved', 'name' => 'Jane Doe',
                                    'createdAt' => '2026-06-01T08:00:00+00:00', 'updatedAt' => '2026-06-24T10:15:00+00:00',
                                    'product'   => ['id' => 2358, 'name' => 'Classic Watch Hand', 'sku' => 'SP-001'],
                                    'customer'  => ['id' => 14, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
                                    'images'    => [['id' => 4, 'path' => 'review/21/photo.webp', 'url' => 'http://localhost:8000/storage/review/21/photo.webp']],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Invalid status value.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/reviews/{id}',
            provider: AdminCustomerReviewWriteProvider::class,
            processor: AdminCustomerReviewProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Delete a review',
                parameters: [
                    new Model\Parameter('id', 'path', 'Review ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Review deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Review deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Review not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/reviews/{id}',
            provider: AdminCustomerReviewProvider::class,
            output: AdminCustomerReviewDetailDto::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Review detail (with linked product + customer)',
                parameters: [
                    new Model\Parameter('id', 'path', 'Review ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Review detail with nested product, customer and images.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 21, 'title' => 'Great product', 'comment' => 'Exactly as described.',
                                    'rating'    => 5, 'status' => 'approved', 'name' => 'Jane Doe',
                                    'createdAt' => '2026-06-01T08:00:00+00:00', 'updatedAt' => '2026-06-20T14:30:00+00:00',
                                    'product'   => ['id' => 2358, 'name' => 'Classic Watch Hand', 'sku' => 'SP-001'],
                                    'customer'  => ['id' => 14, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
                                    'images'    => [['id' => 4, 'path' => 'review/21/photo.webp', 'url' => 'http://localhost:8000/storage/review/21/photo.webp']],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Review not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers/reviews',
            provider: AdminCustomerReviewProvider::class,
            output: AdminCustomerReviewListDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'List customer reviews',
                description: 'Paginated, filterable, sortable list. Returns { data, meta } envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'pending|approved|disapproved', false, schema: ['type' => 'string']),
                    new Model\Parameter('rating', 'query', 'Filter by rating value', false, schema: ['type' => 'integer']),
                    new Model\Parameter('product_id', 'query', 'Filter by product ID', false, schema: ['type' => 'integer']),
                    new Model\Parameter('customer_id', 'query', 'Filter by customer ID', false, schema: ['type' => 'integer']),
                    new Model\Parameter('created_at_from', 'query', 'Lower bound for created_at', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_to', 'query', 'Upper bound for created_at', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'rating', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated reviews in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'        => 21, 'title' => 'Great product', 'comment' => 'Exactly as described.',
                                            'rating'    => 5, 'status' => 'approved', 'name' => 'Jane Doe',
                                            'createdAt' => '2026-06-01T08:00:00+00:00', 'updatedAt' => '2026-06-20T14:30:00+00:00',
                                            'product'   => ['id' => 2358, 'name' => 'Classic Watch Hand', 'sku' => 'SP-001'],
                                            'customer'  => ['id' => 14, 'name' => 'Jane Doe', 'email' => 'jane@example.com'],
                                        ],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 10, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerReviewProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'status'          => ['type' => 'String'],
                'rating'          => ['type' => 'Int'],
                'product_id'      => ['type' => 'Int'],
                'customer_id'     => ['type' => 'Int'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to'   => ['type' => 'String'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
            description: 'Admin customer reviews listing (cursor pagination).',
        ),
        new Query(
            provider: AdminCustomerReviewProvider::class,
            description: 'Admin customer review detail by id.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCustomerReviewUpdateInput::class,
            processor: AdminCustomerReviewProcessor::class,
            description: 'Update review status. Becomes updateAdminCustomerReview.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCustomerReviewUpdateInput::class,
            processor: AdminCustomerReviewProcessor::class,
            description: 'Delete review. Becomes deleteAdminCustomerReview.',
        ),
    ],
)]
class AdminCustomerReview extends EloquentModel
{
    protected $table = 'product_reviews';

    protected $casts = [
        'id'          => 'int',
        'title'       => 'string',
        'comment'     => 'string',
        'rating'      => 'int',
        'status'      => 'string',
        'name'        => 'string',
        'product_id'  => 'int',
        'customer_id' => 'int',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    protected $appends = ['message'];

    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }

    #[ApiProperty(writable: false)]
    public function images(): HasMany
    {
        return $this->hasMany(AdminCustomerReviewImage::class, 'review_id');
    }

    #[ApiProperty(writable: false)]
    public function product(): BelongsTo
    {
        return $this->belongsTo(AdminCustomerReviewProductRef::class, 'product_id');
    }

    #[ApiProperty(writable: false)]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AdminCustomerReviewCustomerRef::class, 'customer_id');
    }
}
