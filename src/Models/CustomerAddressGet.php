<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\Dto\CustomerAddressTokenOutput;
use Webkul\BagistoApi\State\CustomerAddressTokenProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GetCustomerAddress',
    class: CustomerAddressInput::class,
    uriTemplate: '/customer-address-gets',
    operations: [],
    graphQlOperations: [
        new Query(
            name: 'read',
            output: CustomerAddressTokenOutput::class,
            provider: CustomerAddressTokenProvider::class,
            normalizationContext: [
                'groups' => ['query'],
            ],
            description: 'Get single customer address by id using token.',
        ),
    ],
)]
class CustomerAddressGet {}
