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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerDetailDto;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerListDto;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerWriteProvider;

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
                    '201' => new Model\Response(
                        description: 'Customer created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 14, 'firstName' => 'Jane', 'lastName' => 'Doe',
                                    'name'             => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1-202-555-0148',
                                    'gender'           => 'Female', 'dateOfBirth' => '1990-01-01', 'channelId' => 1,
                                    'status'           => 1, 'subscribedToNewsLetter' => false, 'isVerified' => 0, 'isSuspended' => 0,
                                    'totalAddresses'   => 0, 'totalOrders' => 0, 'totalAmountSpent' => 0,
                                    'createdAt'        => '2026-06-24T10:15:00+00:00', 'updatedAt' => '2026-06-24T10:15:00+00:00',
                                    'group'            => ['id' => 2, 'code' => 'wholesale', 'name' => 'Wholesale'],
                                ],
                            ],
                        ]),
                    ),
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
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'first_name'                => ['type' => 'string'],
                                    'last_name'                 => ['type' => 'string'],
                                    'email'                     => ['type' => 'string'],
                                    'phone'                     => ['type' => 'string', 'nullable' => true],
                                    'gender'                    => ['type' => 'string', 'enum' => ['Male', 'Female', 'Other'], 'nullable' => true],
                                    'date_of_birth'             => ['type' => 'string', 'nullable' => true],
                                    'customer_group_id'         => ['type' => 'integer'],
                                    'channel_id'                => ['type' => 'integer', 'nullable' => true],
                                    'status'                    => ['type' => 'integer'],
                                    'subscribed_to_news_letter' => ['type' => 'boolean', 'nullable' => true],
                                    'password'                  => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'example' => [
                                'first_name'        => 'Jane',
                                'last_name'         => 'Doe',
                                'email'             => 'jane@example.com',
                                'phone'             => '+1-202-555-0148',
                                'gender'            => 'Female',
                                'date_of_birth'     => '1990-01-01',
                                'customer_group_id' => 2,
                                'status'            => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Customer updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 14, 'firstName' => 'Jane', 'lastName' => 'Doe',
                                    'name'             => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1-202-555-0148',
                                    'gender'           => 'Female', 'dateOfBirth' => '1990-01-01', 'channelId' => 1,
                                    'status'           => 1, 'subscribedToNewsLetter' => false, 'isVerified' => 0, 'isSuspended' => 0,
                                    'totalAddresses'   => 2, 'totalOrders' => 5, 'totalAmountSpent' => 1240.5,
                                    'createdAt'        => '2026-05-01T09:00:00+00:00', 'updatedAt' => '2026-06-24T10:15:00+00:00',
                                    'group'            => ['id' => 2, 'code' => 'wholesale', 'name' => 'Wholesale'],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
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
                    '200' => new Model\Response(
                        description: 'Customer deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Customer deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Refused — customer has active orders.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/{id}',
            provider: AdminCustomerItemProvider::class,
            output: AdminCustomerDetailDto::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Customer detail',
                description: 'Returns the customer with the linked group as a nested object.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Customer detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'               => 14, 'firstName' => 'Jane', 'lastName' => 'Doe',
                                    'name'             => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1-202-555-0148',
                                    'gender'           => 'Female', 'dateOfBirth' => '1990-01-01', 'channelId' => 1,
                                    'status'           => 1, 'subscribedToNewsLetter' => false, 'isVerified' => 0, 'isSuspended' => 0,
                                    'totalAddresses'   => 2, 'totalOrders' => 5, 'totalAmountSpent' => 1240.5,
                                    'createdAt'        => '2026-05-01T09:00:00+00:00', 'updatedAt' => '2026-06-20T14:30:00+00:00',
                                    'group'            => ['id' => 2, 'code' => 'wholesale', 'name' => 'Wholesale'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Customer not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers',
            provider: AdminCustomerCollectionProvider::class,
            output: AdminCustomerListDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'List customers',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope; each row carries the linked group as a nested object.',
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
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated customers in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'               => 14, 'firstName' => 'Jane', 'lastName' => 'Doe',
                                            'name'             => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1-202-555-0148',
                                            'gender'           => 'Female', 'dateOfBirth' => '1990-01-01', 'channelId' => 1,
                                            'status'           => 1, 'subscribedToNewsLetter' => false, 'isVerified' => 0, 'isSuspended' => 0,
                                            'createdAt'        => '2026-05-01T09:00:00+00:00', 'updatedAt' => '2026-06-20T14:30:00+00:00',
                                            'group'            => ['id' => 2, 'code' => 'wholesale', 'name' => 'Wholesale'],
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
            provider: AdminCustomerCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'               => ['type' => 'String'],
                'email'              => ['type' => 'String'],
                'phone'              => ['type' => 'String'],
                'customer_group_id'  => ['type' => 'Int'],
                'status'             => ['type' => 'Int'],
                'channel_id'         => ['type' => 'Int'],
                'date_of_birth_from' => ['type' => 'String'],
                'date_of_birth_to'   => ['type' => 'String'],
                'created_at_from'    => ['type' => 'String'],
                'created_at_to'      => ['type' => 'String'],
                'sort'               => ['type' => 'String'],
                'order'              => ['type' => 'String'],
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
class AdminCustomer extends EloquentModel
{
    protected $table = 'customers';

    protected $casts = [
        'id'                        => 'int',
        'customer_group_id'         => 'int',
        'channel_id'                => 'int',
        'status'                    => 'int',
        'subscribed_to_news_letter' => 'bool',
        'is_verified'               => 'int',
        'is_suspended'              => 'int',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
    ];

    protected $appends = ['name', 'total_addresses', 'total_orders', 'total_amount_spent', 'message'];

    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }

    #[ApiProperty(writable: false, required: false)]
    public function group(): BelongsTo
    {
        return $this->belongsTo(AdminCustomerGroupRef::class, 'customer_group_id');
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return trim((string) ($this->first_name ?? '').' '.(string) ($this->last_name ?? '')) ?: null;
    }

    #[ApiProperty(writable: false)]
    public function getTotalAddressesAttribute(): int
    {
        return (int) DB::table('addresses')
            ->where('customer_id', $this->id)
            ->where('address_type', 'customer')
            ->count();
    }

    #[ApiProperty(writable: false)]
    public function getTotalOrdersAttribute(): int
    {
        return (int) DB::table('orders')->where('customer_id', $this->id)->count();
    }

    #[ApiProperty(writable: false)]
    public function getTotalAmountSpentAttribute(): float
    {
        return (float) DB::table('orders')
            ->where('customer_id', $this->id)
            ->sum('base_grand_total_invoiced');
    }
}
