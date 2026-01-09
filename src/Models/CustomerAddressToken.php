<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerAddressToken',
    uriTemplate: '/customer-address-tokens',
    operations: [
        new Get(uriTemplate: '/customer-address-tokens/{id}'),
        new GetCollection(uriTemplate: '/customer-address-tokens'),
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
                'groups'                 => ['mutation'],
            ],
            description: 'Add new or update existing customer address using token',
        ),
        new Mutation(
            name: 'createDelete',
            input: CustomerAddressInput::class,
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Delete customer address using token',
        ),
        new Query(
            name: 'read',
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            normalizationContext: [
                'groups'                 => ['query'],
            ],
            description: 'Get single customer address by ID using token',
        ),
        new Query(
            name: 'collection',
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            normalizationContext: [
                'groups'                 => ['query'],
            ],
            description: 'Get all customer addresses using token',
        ),
    ]
)]
class CustomerAddressToken
{
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $addressId = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $firstName = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $lastName = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $address1 = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $address2 = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $country = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $state = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $city = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $postcode = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $useForShipping = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $defaultAddress = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?array $addresses = null;
}
