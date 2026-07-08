<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CustomerProfileInput;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\State\AuthenticatedCustomerProvider;
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
        new Put(
            uriTemplate: '/customer-profile-updates/{id}',
            input: CustomerProfileInput::class,
            output: CustomerProfileOutput::class,
            provider: AuthenticatedCustomerProvider::class,
            processor: CustomerProfileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Update customer profile',
                description: 'Update the authenticated customer\'s profile. Requires Bearer token. Fields isVerified, isSuspended, and status cannot be changed by the customer.',
                requestBody: new RequestBody(
                    description: 'Customer profile fields to update. Only include the fields you want to change.',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'firstName' => ['type' => 'string', 'example' => 'John'],
                                    'lastName' => ['type' => 'string', 'example' => 'Doe'],
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'phone' => ['type' => 'string', 'example' => '1234567890'],
                                    'gender' => ['type' => 'string', 'enum' => ['Male', 'Female', 'Other'], 'example' => 'Male'],
                                    'dateOfBirth' => ['type' => 'string', 'format' => 'date', 'example' => '1990-01-15'],
                                    'currentPassword' => ['type' => 'string', 'format' => 'password', 'example' => 'OldPassword123!', 'description' => 'Current password — required only when changing password'],
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'NewPassword123!', 'description' => 'New password (requires currentPassword and confirmPassword)'],
                                    'confirmPassword' => ['type' => 'string', 'format' => 'password', 'example' => 'NewPassword123!', 'description' => 'Must match password'],
                                    'subscribedToNewsLetter' => ['type' => 'boolean', 'example' => true],
                                    'image' => ['type' => 'string', 'example' => 'data:image/jpeg;base64,...', 'description' => 'Profile image as base64 encoded string'],
                                    'deleteImage' => ['type' => 'boolean', 'example' => false, 'description' => 'Set true to remove existing profile image'],
                                ],
                            ],
                            'example' => [
                                'first_name' => 'Api',
                                'last_name' => 'DocUpdated',
                                'phone' => '9998887777',
                            ],
                            'examples' => [
                                'update_partial_profile' => [
                                    'summary' => 'Update selected fields',
                                    'value' => [
                                        'first_name' => 'Api',
                                        'last_name' => 'DocUpdated',
                                        'phone' => '9998887777',
                                    ],
                                ],
                                'update_full_profile' => [
                                    'summary' => 'Update full profile',
                                    'value' => [
                                        'firstName' => 'John',
                                        'lastName' => 'Doe',
                                        'email' => 'john@example.com',
                                        'phone' => '1234567890',
                                        'gender' => 'Male',
                                        'dateOfBirth' => '1990-01-15',
                                        'subscribedToNewsLetter' => true,
                                        'password' => 'NewPassword123!',
                                        'confirmPassword' => 'NewPassword123!',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Response(
                        description: 'Customer profile updated successfully',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => '1529',
                                    '_id' => '1529',
                                    'firstName' => 'Api',
                                    'lastName' => 'DocUpdated',
                                    'email' => 'john@example.com',
                                    'phone' => '9998887777',
                                    'status' => '1',
                                    'subscribedToNewsLetter' => false,
                                    'isVerified' => 'false',
                                    'isSuspended' => 'false',
                                    'success' => true,
                                    'message' => 'Customer profile updated successfully',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerProfileInput::class,
            output: CustomerProfileOutput::class,
            processor: CustomerProfileProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            description: 'Update authenticated customer profile (requires token and at least one field). Re-query readCustomerProfile for updated data.',
        ),
    ]
)]
class CustomerProfileUpdate
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $dateOfBirth = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $currentPassword = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $confirmPassword = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?bool $subscribedToNewsLetter = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $isVerified = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $isSuspended = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: true)]
    #[Groups(['mutation'])]
    public ?bool $deleteImage = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
