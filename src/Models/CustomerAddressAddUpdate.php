<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'AddUpdateCustomerAddress',
    class: CustomerAddressInput::class,
    uriTemplate: '/customer-address-add-updates',
    operations: [
        new Get(uriTemplate: '/customer-address-add-updates/{id}'),
        new GetCollection(uriTemplate: '/customer-address-add-updates'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerAddressInput::class,
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Add or update customer address using token. If id is provided, updates the address; otherwise creates new address.',
        ),
    ],
)]
class CustomerAddressAddUpdate {}
