<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\Customer\Models\CustomerAddress as CustomerAddressModel;

/**
 * API resource for querying customer addresses with cursor-based pagination.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GetCustomerAddresses',
    uriTemplate: '/customer-addresses-filter',
    operations: [
        new GetCollection(provider: CustomerAddressProvider::class),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CustomerAddressProvider::class,
            args: [
                'token' => ['type' => 'String!', 'description' => 'Customer Auth token (required)'],
            ],
            paginationPartial: false,
            paginationType: 'cursor',
        ),
    ]
)]
class CustomerAddressGetAll extends CustomerAddressModel {}
