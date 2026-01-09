<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\Checkout\Models\CartAddress;

/**
 * MutationCheckoutAddress - BagistoApi Mutation for Checkout Address
 *
 * Dedicated resource for the mutation that exposes all CartAddress fields
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutAddressInput::class,
            output: 'Webkul\BagistoApi\Models\CheckoutAddressPayloadOutput',
            processor: CheckoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Save billing and shipping addresses for checkout. Returns the created address.',
        ),
    ]
)]
class MutationCheckoutAddress extends CartAddress
{
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $addressType = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $parentAddressId = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $orderId = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $firstName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $lastName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $gender = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $companyName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $address = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $city = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $state = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $country = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $postcode = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $vatId = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $defaultAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $useForShipping = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $additional = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $createdAt = null;

    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $updatedAt = null;
}
