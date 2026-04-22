<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;
use Webkul\Customer\Models\CustomerAddress as CustomerAddressModel;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/customer-addresses/{id}',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-addresses',
            provider: CustomerAddressProvider::class,
            openapi: new Model\Operation(
                tags: ['Customer Address'],
                summary: 'Get all customer addresses',
                description: 'Returns all addresses of the authenticated customer. Requires Bearer token.',
            ),
        ),
        new Get(
            uriTemplate: '/customer-addresses/{id}',
            openapi: new Model\Operation(
                tags: ['Customer Address'],
                summary: 'Get a customer address',
                description: 'Returns a specific address by ID. Requires Bearer token.',
            ),
        ),
        new Post(
            uriTemplate: '/customer-addresses',
            input: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['Customer Address'],
                summary: 'Create a customer address',
                description: 'Create a new address for the authenticated customer. Requires Bearer token.',
                requestBody: new Model\RequestBody(
                    description: 'Address data',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['firstName', 'lastName', 'email', 'phone', 'address1', 'city', 'state', 'postcode', 'country'],
                                'properties' => [
                                    'firstName'      => ['type' => 'string', 'example' => 'John'],
                                    'lastName'       => ['type' => 'string', 'example' => 'Doe'],
                                    'email'          => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'phone'          => ['type' => 'string', 'example' => '1234567890'],
                                    'address1'       => ['type' => 'string', 'example' => '123 Main St'],
                                    'address2'       => ['type' => 'string', 'example' => 'Apt 4B'],
                                    'city'           => ['type' => 'string', 'example' => 'New York'],
                                    'state'          => ['type' => 'string', 'example' => 'NY'],
                                    'postcode'       => ['type' => 'string', 'example' => '10001'],
                                    'country'        => ['type' => 'string', 'example' => 'US'],
                                    'defaultAddress' => ['type' => 'boolean', 'example' => true, 'description' => 'Set as default address'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Put(
            uriTemplate: '/customer-addresses/{id}',
            input: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['Customer Address'],
                summary: 'Update a customer address',
                description: 'Update an existing address. Requires Bearer token. Include addressId in the body.',
                requestBody: new Model\RequestBody(
                    description: 'Address fields to update',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'firstName'      => ['type' => 'string', 'example' => 'John'],
                                    'lastName'       => ['type' => 'string', 'example' => 'Doe'],
                                    'email'          => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'phone'          => ['type' => 'string', 'example' => '1234567890'],
                                    'address1'       => ['type' => 'string', 'example' => '456 Oak Ave'],
                                    'address2'       => ['type' => 'string', 'example' => 'Suite 100'],
                                    'city'           => ['type' => 'string', 'example' => 'Los Angeles'],
                                    'state'          => ['type' => 'string', 'example' => 'CA'],
                                    'postcode'       => ['type' => 'string', 'example' => '90001'],
                                    'country'        => ['type' => 'string', 'example' => 'US'],
                                    'defaultAddress' => ['type' => 'boolean', 'example' => false, 'description' => 'Set as default address'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Delete(
            uriTemplate: '/customer-addresses/{id}',
            processor: CustomerAddressTokenProcessor::class,
            openapi: new Model\Operation(
                tags: ['Customer Address'],
                summary: 'Delete a customer address',
                description: 'Delete an address by ID. Requires Bearer token.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class CustomerAddress extends CustomerAddressModel {}
