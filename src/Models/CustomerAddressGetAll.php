<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\Customer\Models\CustomerAddress as CustomerAddressModel;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GetCustomerAddresses',
    uriTemplate: '/customer-addresses',
    operations: [],
    graphQlOperations: [
        new QueryCollection(
            provider: CustomerAddressProvider::class,
            paginationPartial: false,
            paginationType: 'cursor',
            extraArgs: [
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
    ]
)]
class CustomerAddressGetAll extends CustomerAddressModel {}
