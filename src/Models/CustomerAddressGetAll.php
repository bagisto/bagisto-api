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
            description: 'The authenticated customer\'s saved addresses. `sort` is `id` or `created_at` (or `column-asc`/`column-desc`) and `order` is `asc` (default) or `desc`. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
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
