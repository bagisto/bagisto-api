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
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerWriteProvider;

/**
 * Admin Customers — full CRUD.
 *
 * REST:
 *   GET    /api/admin/customers
 *   GET    /api/admin/customers/{id}
 *   POST   /api/admin/customers
 *   PUT    /api/admin/customers/{id}
 *   DELETE /api/admin/customers/{id}
 *
 * GraphQL: adminCustomers, adminCustomer,
 *          createAdminCustomer, updateAdminCustomer, deleteAdminCustomer
 *
 * Mirrors Webkul\Admin\Http\Controllers\Customers\CustomerController.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomer',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers',
            input: AdminCustomerCreateInput::class,
            processor: AdminCustomerProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Create a new customer',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['first_name', 'last_name', 'email', 'customer_group_id'],
                                'properties' => [
                                    'first_name'                => ['type' => 'string', 'example' => 'Jane'],
                                    'last_name'                 => ['type' => 'string', 'example' => 'Doe'],
                                    'email'                     => ['type' => 'string', 'example' => 'jane@example.com'],
                                    'phone'                     => ['type' => 'string', 'nullable' => true],
                                    'gender'                    => ['type' => 'string', 'enum' => ['Male', 'Female', 'Other'], 'nullable' => true],
                                    'date_of_birth'             => ['type' => 'string', 'nullable' => true, 'example' => '1990-01-01'],
                                    'customer_group_id'         => ['type' => 'integer', 'example' => 2],
                                    'channel_id'                => ['type' => 'integer', 'nullable' => true],
                                    'status'                    => ['type' => 'integer', 'example' => 1],
                                    'subscribed_to_news_letter' => ['type' => 'boolean', 'nullable' => true],
                                    'send_password'             => ['type' => 'boolean', 'example' => true, 'description' => 'When true, generate a random password and email credentials. When false, the explicit password field is required.'],
                                    'password'                  => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Customer created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/customers/{id}',
            input: AdminCustomerUpdateInput::class,
            provider: AdminCustomerWriteProvider::class,
            processor: AdminCustomerProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Update a customer',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/{id}',
            provider: AdminCustomerWriteProvider::class,
            processor: AdminCustomerProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Delete a customer',
                description: 'Refuses if the customer has any pending/processing orders (400).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Customer deleted.'),
                    '400' => new Model\Response(description: 'Refused — customer has active orders.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/{id}',
            provider: AdminCustomerItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Customer detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers',
            provider: AdminCustomerCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'List customers',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('name', 'query', 'Filter by first/last name (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('email', 'query', 'Filter by email (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('phone', 'query', 'Filter by phone (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('customer_group_id', 'query', 'Filter by customer group ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'Filter by status (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('channel_id', 'query', 'Filter by channel ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'email', 'first_name']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'              => ['type' => 'String'],
                'email'             => ['type' => 'String'],
                'phone'             => ['type' => 'String'],
                'customer_group_id' => ['type' => 'Int'],
                'status'            => ['type' => 'Int'],
                'channel_id'        => ['type' => 'Int'],
                'sort'              => ['type' => 'String'],
                'order'             => ['type' => 'String'],
            ],
            description: 'Admin customers listing (cursor pagination).',
        ),
        new Query(
            provider: AdminCustomerItemProvider::class,
            description: 'Admin customer detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminCustomerCreateInput::class,
            processor: AdminCustomerProcessor::class,
            description: 'Create a new customer. Becomes createAdminCustomer.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCustomerUpdateInput::class,
            processor: AdminCustomerProcessor::class,
            description: 'Update a customer. Becomes updateAdminCustomer.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCustomerUpdateInput::class,
            processor: AdminCustomerProcessor::class,
            description: 'Delete a customer. Becomes deleteAdminCustomer.',
        ),
    ],
)]
class AdminCustomer
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $firstName = null;

    #[ApiProperty(writable: false)]
    public ?string $lastName = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $email = null;

    #[ApiProperty(writable: false)]
    public ?string $phone = null;

    #[ApiProperty(writable: false)]
    public ?string $gender = null;

    #[ApiProperty(writable: false)]
    public ?string $dateOfBirth = null;

    #[ApiProperty(writable: false)]
    public ?int $customerGroupId = null;

    #[ApiProperty(writable: false)]
    public ?string $customerGroupName = null;

    #[ApiProperty(writable: false)]
    public ?int $channelId = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?bool $subscribedToNewsLetter = null;

    #[ApiProperty(writable: false)]
    public ?int $isVerified = null;

    #[ApiProperty(writable: false)]
    public ?int $isSuspended = null;

    /** Detail-only — null on listing rows */
    #[ApiProperty(writable: false)]
    public ?int $totalAddresses = null;

    #[ApiProperty(writable: false)]
    public ?int $totalOrders = null;

    #[ApiProperty(writable: false)]
    public ?float $totalAmountSpent = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
