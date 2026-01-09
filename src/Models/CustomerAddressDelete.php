<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'DeleteCustomerAddress',
    class: CustomerAddressInput::class,
    uriTemplate: '/customer-address-deletes',
    operations: [
        new Delete(uriTemplate: '/customer-address-deletes/{id}'),
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
            description: 'Delete customer address using token. Requires address id.',
        ),
    ],
)]
class CustomerAddressDelete {}
