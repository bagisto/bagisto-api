<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\Dto\CustomerAddressTokenOutput;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;
use Webkul\BagistoApi\State\CustomerAddressTokenProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerAddressToken',
    uriTemplate: '/customer-address-tokens',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerAddressInput::class,
            output: CustomerAddressInput::class,
            processor: CustomerAddressTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Delete customer address using token',
        ),
        new Query(
            name: 'read',
            output: CustomerAddressTokenOutput::class,
            provider: CustomerAddressTokenProvider::class,
            normalizationContext: [
                'groups' => ['query'],
            ],
            description: 'Get single customer address by ID using token',
        ),
    ]
)]
class CustomerAddressToken
{
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    /**
     * The address id.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $addressId = null;

    /**
     * The first name.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $firstName = null;

    /**
     * The last name.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $lastName = null;

    /**
     * The email.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    /**
     * The address1.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $address1 = null;

    /**
     * The address2.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $address2 = null;

    /**
     * The country.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $country = null;

    /**
     * The state.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $state = null;

    /**
     * The city.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $city = null;

    /**
     * The postcode.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $postcode = null;

    /**
     * The use for shipping.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $useForShipping = null;

    /**
     * The default address.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $defaultAddress = null;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    /**
     * The addresses.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?array $addresses = null;
}
