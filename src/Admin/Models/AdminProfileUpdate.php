<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminProfileInput;
use Webkul\BagistoApi\Admin\State\AdminProfileProcessor;

/**
 * Update the authenticated admin's own profile.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminProfileUpdate',
    operations: [
        new Post(
            uriTemplate: '/update',
            input: AdminProfileInput::class,
            output: self::class,
            processor: AdminProfileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: ['groups' => ['mutation'], 'skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Admin Authentication'],
                summary: "Update admin user's profile",
                description: "Update the authenticated admin's name, email, or password. currentPassword is required for any update.",
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['currentPassword'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'John Admin'],
                                    'email'           => ['type' => 'string', 'format' => 'email', 'example' => 'admin@example.com'],
                                    'currentPassword' => ['type' => 'string', 'format' => 'password', 'example' => 'admin123'],
                                    'password'        => ['type' => 'string', 'format' => 'password', 'example' => 'NewPass123!'],
                                    'confirmPassword' => ['type' => 'string', 'format' => 'password', 'example' => 'NewPass123!'],
                                ],
                            ],
                            'example' => [
                                'name'            => 'John Admin',
                                'email'           => 'admin@example.com',
                                'currentPassword' => 'admin123',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Profile updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'      => '1',
                                    'name'    => 'John Admin',
                                    'email'   => 'admin@example.com',
                                    'success' => true,
                                    'message' => 'Account updated successfully.',
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
            input: AdminProfileInput::class,
            output: self::class,
            processor: AdminProfileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: ['groups' => ['mutation']],
        ),
    ]
)]
class AdminProfileUpdate
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
