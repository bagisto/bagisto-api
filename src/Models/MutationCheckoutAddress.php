<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\Checkout\Models\CartAddress;

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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Save billing and shipping addresses for checkout. Returns the created address.',
        ),
    ]
)]
class MutationCheckoutAddress extends CartAddress
{
    /**
     * The id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    /**
     * The address type.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $addressType = null;

    /**
     * The parent address id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $parentAddressId = null;

    /**
     * The order id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $orderId = null;

    /**
     * The first name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $firstName = null;

    /**
     * The last name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $lastName = null;

    /**
     * The gender.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $gender = null;

    /**
     * The company name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $companyName = null;

    /**
     * The address.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $address = null;

    /**
     * The city.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $city = null;

    /**
     * The state.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $state = null;

    /**
     * The country.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $country = null;

    /**
     * The postcode.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $postcode = null;

    /**
     * The email.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    /**
     * The vat id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $vatId = null;

    /**
     * The default address.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $defaultAddress = null;

    /**
     * The use for shipping.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $useForShipping = null;

    /**
     * The additional.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $additional = null;

    /**
     * The created at.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $createdAt = null;

    /**
     * The updated at.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $updatedAt = null;
}
