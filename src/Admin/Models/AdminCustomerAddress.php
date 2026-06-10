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
use Webkul\BagistoApi\Admin\Dto\AdminCustomerAddressCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerAddressUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressWriteProvider;

/**
 * Customer Address — listing + full CRUD (Block C C1).
 *
 * REST:
 *   GET    /api/admin/customers/{customerId}/addresses
 *   GET    /api/admin/customers/{customerId}/addresses/{id}
 *   POST   /api/admin/customers/{customerId}/addresses
 *   PUT    /api/admin/customers/{customerId}/addresses/{id}
 *   DELETE /api/admin/customers/{customerId}/addresses/{id}
 *
 * GraphQL: adminCustomerAddresses, adminCustomerAddress,
 *          createAdminCustomerAddress, updateAdminCustomerAddress, deleteAdminCustomerAddress
 *
 * Mirrors Webkul\Admin\Http\Controllers\Customers\AddressController.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerAddress',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/addresses',
            provider: AdminCustomerAddressProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Get a customer's saved addresses",
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/{customerId}/addresses/{id}',
            provider: AdminCustomerAddressItemProvider::class,
            requirements: ['customerId' => '\d+', 'id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Address detail',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'path', 'Address ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/customers/{customerId}/addresses',
            input: AdminCustomerAddressCreateInput::class,
            processor: AdminCustomerAddressProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Create address for a customer',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['first_name', 'last_name', 'address', 'city', 'country', 'postcode', 'phone'],
                                'properties' => [
                                    'first_name'      => ['type' => 'string'],
                                    'last_name'       => ['type' => 'string'],
                                    'company_name'    => ['type' => 'string', 'nullable' => true],
                                    'vat_id'          => ['type' => 'string', 'nullable' => true],
                                    'address'         => ['type' => 'string'],
                                    'city'            => ['type' => 'string'],
                                    'state'           => ['type' => 'string', 'nullable' => true],
                                    'country'         => ['type' => 'string'],
                                    'postcode'        => ['type' => 'string'],
                                    'phone'           => ['type' => 'string'],
                                    'email'           => ['type' => 'string', 'nullable' => true],
                                    'default_address' => ['type' => 'boolean'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Put(
            uriTemplate: '/customers/{customerId}/addresses/{id}',
            input: AdminCustomerAddressUpdateInput::class,
            provider: AdminCustomerAddressWriteProvider::class,
            processor: AdminCustomerAddressProcessor::class,
            requirements: ['customerId' => '\d+', 'id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Update address',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'path', 'Address ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/{customerId}/addresses/{id}',
            provider: AdminCustomerAddressWriteProvider::class,
            processor: AdminCustomerAddressProcessor::class,
            requirements: ['customerId' => '\d+', 'id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Delete address',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'path', 'Address ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/customers/{customerId}/addresses/{id}/set-default',
            provider: AdminCustomerAddressWriteProvider::class,
            processor: AdminCustomerAddressProcessor::class,
            requirements: ['customerId' => '\d+', 'id' => '\d+'],
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Set a customer address as the default',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'path', 'Address ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerAddressProvider::class,
            paginationType: 'cursor',
            description: 'All addresses belonging to a customer.',
            args: [
                'customerId' => ['type' => 'Int!', 'description' => 'Customer ID'],
            ],
        ),
        new Query(
            provider: AdminCustomerAddressItemProvider::class,
            description: 'Single customer address by id.',
            args: [
                'customerId' => ['type' => 'Int!'],
                'id'         => ['type' => 'ID!'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminCustomerAddressCreateInput::class,
            processor: AdminCustomerAddressProcessor::class,
            description: 'Create customer address. Becomes createAdminCustomerAddress.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCustomerAddressUpdateInput::class,
            processor: AdminCustomerAddressProcessor::class,
            description: 'Update customer address. Becomes updateAdminCustomerAddress.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCustomerAddressUpdateInput::class,
            processor: AdminCustomerAddressProcessor::class,
            description: 'Delete customer address. Becomes deleteAdminCustomerAddress.',
        ),
        new Mutation(
            name: 'setDefault',
            input: AdminCustomerAddressUpdateInput::class,
            processor: AdminCustomerAddressProcessor::class,
            description: 'Set a customer address as the default. Becomes setDefaultAdminCustomerAddress.',
        ),
    ]
)]
class AdminCustomerAddress
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $address_type = null;

    #[ApiProperty(writable: false)]
    public ?string $first_name = null;

    #[ApiProperty(writable: false)]
    public ?string $last_name = null;

    #[ApiProperty(writable: false)]
    public ?string $company_name = null;

    #[ApiProperty(writable: false)]
    public ?string $address = null;

    #[ApiProperty(writable: false)]
    public ?string $city = null;

    #[ApiProperty(writable: false)]
    public ?string $state = null;

    #[ApiProperty(writable: false)]
    public ?string $country = null;

    #[ApiProperty(writable: false)]
    public ?string $postcode = null;

    #[ApiProperty(writable: false)]
    public ?string $email = null;

    #[ApiProperty(writable: false)]
    public ?string $phone = null;

    #[ApiProperty(writable: false)]
    public ?string $vat_id = null;

    #[ApiProperty(writable: false)]
    public ?bool $default_address = null;
}
