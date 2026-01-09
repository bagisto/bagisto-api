<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GetCustomerAddress',
    class: CustomerAddressInput::class,
    uriTemplate: '/customer-address-gets',
    operations: [
        new Get(uriTemplate: '/customer-address-gets/{id}'),
        new GetCollection(uriTemplate: '/customer-address-gets'),
    ],
    graphQlOperations: [
        new Query(
            name: 'read',
            input: CustomerAddressInput::class,
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['query'],
            ],
            normalizationContext: [
                'groups' => ['query'],
            ],
            description: 'Get single customer address by id using token.',
        ),
    ],
)]
class CustomerAddressGet {}
