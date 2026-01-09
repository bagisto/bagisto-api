<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Put;
use Webkul\BagistoApi\Dto\CustomerProfileInput;
use Webkul\BagistoApi\State\CustomerProfileProcessor;

/**
 * Customer profile update resource
 * Handles authenticated customer profile updates
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerProfileUpdate',
    uriTemplate: '/customer-profile-updates',
    operations: [
        new Put(uriTemplate: '/customer-profile-updates/{id}'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerProfileInput::class,
            output: CustomerProfileUpdate::class,
            processor: CustomerProfileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Update authenticated customer profile (requires token and at least one field)',
        ),
    ]
)]
class CustomerProfileUpdate
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $token = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $first_name = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $last_name = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $date_of_birth = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $password = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $confirm_password = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $subscribed_to_news_letter = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $is_verified = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $is_suspended = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;
}
