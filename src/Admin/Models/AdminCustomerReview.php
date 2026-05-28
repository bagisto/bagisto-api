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
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewWriteProvider;

/**
 * Admin Customer Reviews — moderation only (no create).
 *
 * REST:
 *   GET    /api/admin/customers/reviews
 *   GET    /api/admin/customers/reviews/{id}
 *   PUT    /api/admin/customers/reviews/{id}     (status moderation)
 *   DELETE /api/admin/customers/reviews/{id}
 *
 * GraphQL: adminCustomerReviews, adminCustomerReview,
 *          updateAdminCustomerReview, deleteAdminCustomerReview
 *
 * Mirrors Webkul\Admin\Http\Controllers\Customers\ReviewController.
 */
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
            ),
        ),
        new Get(
            uriTemplate: '/customers/reviews/{id}',
            provider: AdminCustomerReviewItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer Reviews'],
                summary: 'Review detail (with linked product + customer)',
                parameters: [
                    new Model\Parameter('id', 'path', 'Review ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers/reviews',
            provider: AdminCustomerReviewCollectionProvider::class,
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
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerReviewCollectionProvider::class,
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
            provider: AdminCustomerReviewItemProvider::class,
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
class AdminCustomerReview
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $title = null;

    #[ApiProperty(writable: false)]
    public ?string $comment = null;

    #[ApiProperty(writable: false)]
    public ?int $rating = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?int $productId = null;

    #[ApiProperty(writable: false)]
    public ?string $productName = null;

    #[ApiProperty(writable: false)]
    public ?string $productSku = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?string $customerName = null;

    #[ApiProperty(writable: false)]
    public ?string $customerEmail = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;

    /** Detail-only: review attachment image urls */
    #[ApiProperty(writable: false)]
    public ?array $images = null;
}
