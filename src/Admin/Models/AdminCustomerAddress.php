<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProvider;

/**
 * Slim address row for the Create-Order screen's "saved addresses" picker.
 *
 * REST  : GET /api/admin/customers/{customerId}/addresses → { data, meta }
 * GraphQL: adminCustomerAddresses(customerId:) → cursor connection
 *
 * Read-only; not paginated by the UI but returned through the standard admin
 * collection envelope for consistency.
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
                description: "All addresses belonging to the given customer — used by the Create-Order screen's billing / shipping picker.",
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'List of the customer\'s saved addresses.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'              => 4943,
                                            'addressType'     => 'customer',
                                            'firstName'       => 'John',
                                            'lastName'        => 'Doe',
                                            'companyName'     => 'Acme Inc.',
                                            'address'         => '123 Main St',
                                            'city'            => 'New York',
                                            'state'           => 'NY',
                                            'country'         => 'US',
                                            'postcode'        => '10001',
                                            'email'           => 'john@example.com',
                                            'phone'           => '1234567890',
                                            'vatId'           => null,
                                            'defaultAddress'  => true,
                                        ],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
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
            provider: AdminCustomerAddressProvider::class,
            paginationType: 'cursor',
            description: 'All addresses belonging to a customer.',
            args: [
                'customerId' => ['type' => 'Int!', 'description' => 'Customer ID'],
            ],
        ),
    ]
)]
class AdminCustomerAddress
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $addressType = null;

    #[ApiProperty(writable: false)]
    public ?string $firstName = null;

    #[ApiProperty(writable: false)]
    public ?string $lastName = null;

    #[ApiProperty(writable: false)]
    public ?string $companyName = null;

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
    public ?string $vatId = null;

    #[ApiProperty(writable: false)]
    public ?bool $defaultAddress = null;
}
