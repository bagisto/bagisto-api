<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\Customer\Models\CustomerAddress as CustomerAddressModel;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/customer-addresses/{id}',
    operations: [
        new Get(uriTemplate: '/customer-addresses/{id}'),
        new GetCollection(uriTemplate: '/customer-addresses'),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class CustomerAddress extends CustomerAddressModel {}
