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
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGdprUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprWriteProvider;

/**
 * Admin Customer GDPR Requests — read / status-update / delete.
 *
 * REST:
 *   GET    /api/admin/customers/gdpr-requests
 *   GET    /api/admin/customers/gdpr-requests/{id}
 *   PUT    /api/admin/customers/gdpr-requests/{id}
 *   DELETE /api/admin/customers/gdpr-requests/{id}
 *
 * GraphQL: adminCustomerGdprRequests, adminCustomerGdprRequest,
 *          updateAdminCustomerGdprRequest, deleteAdminCustomerGdprRequest
 *
 * Mirrors Webkul\Admin\Http\Controllers\Customers\GDPRController. The admin
 * panel's only writes are status update + delete; the destructive cascade for
 * type=delete requests happens via the separate /process action (see
 * AdminCustomerGdprProcess) — matches what the customer-facing side does on
 * approval but exposes it as an explicit endpoint for API integrators.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerGdprRequest',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Put(
            uriTemplate: '/customers/gdpr-requests/{id}',
            input: AdminCustomerGdprUpdateInput::class,
            provider: AdminCustomerGdprWriteProvider::class,
            processor: AdminCustomerGdprProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'Update a GDPR request',
                description: 'Update the request status. Allowed status values: pending, processing, declined, approved. Use the /process endpoint to also perform the destructive side-effect for delete requests.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Request ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'enum' => ['pending', 'processing', 'declined', 'approved', 'revoked'], 'example' => 'processing'],
                                    'message' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'example' => ['status' => 'processing', 'message' => 'Reviewing the request.'],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'GDPR request updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9, 'customerId' => 14, 'customerName' => 'Jane Doe',
                                    'email' => 'jane@example.com', 'type' => 'delete', 'status' => 'processing',
                                    'message' => 'Reviewing the request.', 'revokedAt' => null,
                                    'createdAt' => '2026-06-10T09:00:00+00:00', 'updatedAt' => '2026-06-24T10:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Invalid status value.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/gdpr-requests/{id}',
            provider: AdminCustomerGdprWriteProvider::class,
            processor: AdminCustomerGdprProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'Delete a GDPR request',
                parameters: [
                    new Model\Parameter('id', 'path', 'Request ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'GDPR request deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'GDPR request deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'GDPR request not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/gdpr-requests/{id}',
            provider: AdminCustomerGdprItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'GDPR request detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Request ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'GDPR request detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9, 'customerId' => 14, 'customerName' => 'Jane Doe',
                                    'email' => 'jane@example.com', 'type' => 'delete', 'status' => 'pending',
                                    'message' => 'Please remove my account.', 'revokedAt' => null,
                                    'createdAt' => '2026-06-10T09:00:00+00:00', 'updatedAt' => '2026-06-10T09:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'GDPR request not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers/gdpr-requests',
            provider: AdminCustomerGdprCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customer GDPR'],
                summary: 'List GDPR requests',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'Filter by status.', false, schema: ['type' => 'string']),
                    new Model\Parameter('type', 'query', 'Filter by type (update or delete).', false, schema: ['type' => 'string']),
                    new Model\Parameter('customer_id', 'query', 'Filter by customer id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('email', 'query', 'Filter by email (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('customer_name', 'query', 'Filter by customer name (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_from', 'query', 'Filter by created_at >= (YYYY-MM-DD).', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_to', 'query', 'Filter by created_at <= (YYYY-MM-DD).', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'status', 'type', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated GDPR requests in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 9, 'customerId' => 14, 'customerName' => 'Jane Doe',
                                            'email' => 'jane@example.com', 'type' => 'delete', 'status' => 'pending',
                                            'message' => 'Please remove my account.', 'revokedAt' => null,
                                            'createdAt' => '2026-06-10T09:00:00+00:00', 'updatedAt' => '2026-06-10T09:00:00+00:00',
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
            provider: AdminCustomerGdprCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'status' => ['type' => 'String'],
                'type' => ['type' => 'String'],
                'customer_id' => ['type' => 'Int'],
                'email' => ['type' => 'String'],
                'customer_name' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin customer GDPR requests listing (cursor pagination).',
        ),
        new Query(
            provider: AdminCustomerGdprItemProvider::class,
            description: 'Admin customer GDPR request detail by id.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCustomerGdprUpdateInput::class,
            processor: AdminCustomerGdprProcessor::class,
            description: 'Update a GDPR request. Becomes updateAdminCustomerGdprRequest.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCustomerGdprUpdateInput::class,
            processor: AdminCustomerGdprProcessor::class,
            description: 'Delete a GDPR request. Becomes deleteAdminCustomerGdprRequest.',
        ),
    ],
)]
class AdminCustomerGdprRequest
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    #[ApiProperty(writable: false)]
    public ?string $email = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;

    #[ApiProperty(writable: false)]
    public ?string $revoked_at = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
